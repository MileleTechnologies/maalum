<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dompdf\Dompdf;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'vendor/autoload.php'; // Composer autoloader (Dompdf + optional phpdotenv)

// Optionally load environment variables
if (file_exists(__DIR__ . '/.env')) {
    try {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
    } catch (Throwable $t) {
        // ignore dotenv load failure
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $errors = [];

    // CSRF protection
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        http_response_code(400);
        echo 'Invalid request (CSRF token mismatch).';
        exit;
    }

    // Collect and validate form data
    $bookingName = trim((string)($_POST['bookname'] ?? ''));
    $supervisorsRaw = $_POST['Supervissor'] ?? [];
    if (!is_array($supervisorsRaw)) {
        $supervisorsRaw = [$supervisorsRaw];
    }
    $supervisorsArr = array_values(array_filter(array_map(function ($v) {
        return trim((string)$v);
    }, $supervisorsRaw), function ($v) {
        return $v !== '';
    }));
    $supervisorDisplay = implode(', ', $supervisorsArr);

    $email = trim((string)($_POST['email'] ?? ''));
    $telephone = trim((string)($_POST['telephone'] ?? ''));
    $sigMode = (string)($_POST['sig_mode'] ?? 'draw');
    $signatureData = (string)($_POST['signature'] ?? ''); // data URL from canvas (draw)
    $signatureText = trim((string)($_POST['signature_text'] ?? '')); // typed signature

    // New fields: booking date and children info
    $bookingDate = trim((string)($_POST['booking_date'] ?? ''));
    $hasChildren = isset($_POST['has_children']);
    $childrenCount = isset($_POST['children_count']) ? (int)$_POST['children_count'] : 0;
    $childrenAges = [];
    $childrenRespAck = isset($_POST['children_responsibility']);

    // Required fields
    if ($bookingName === '') {
        $errors[] = 'Booking name is required.';
    }
    if (empty($supervisorsArr)) {
        $errors[] = 'At least one supervisor name is required.';
    }
    if ($email === '' && $telephone === '') {
        $errors[] = 'Provide at least an email or a telephone number.';
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }
    if (!isset($_POST['agree_terms'])) {
        $errors[] = 'You must agree to the terms & conditions.';
    }

    // Validate booking date: must be provided and not in the past (relative to APP_TZ)
    if ($bookingDate === '') {
        $errors[] = 'Booking date is required.';
    } else {
        $appTz = $_ENV['APP_TZ'] ?? 'UTC';
        try {
            $today = new DateTime('now', new DateTimeZone($appTz));
        } catch (Throwable $t) {
            $today = new DateTime('now', new DateTimeZone('UTC'));
        }
        $todayStr = $today->format('Y-m-d');
        // Basic format check YYYY-MM-DD and compare lexicographically (safe for ISO date format)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $bookingDate)) {
            $errors[] = 'Invalid booking date format.';
        } elseif ($bookingDate < $todayStr) {
            $errors[] = 'Booking date cannot be in the past.';
        }
    }

    // Validate children section if present
    if ($hasChildren) {
        if ($childrenCount <= 0) {
            $errors[] = 'Please specify the number of children.';
        } else {
            for ($i = 1; $i <= $childrenCount; $i++) {
                $key = 'child_age_' . $i;
                $val = isset($_POST[$key]) ? trim((string)$_POST[$key]) : '';
                if ($val === '' || !ctype_digit($val)) {
                    $errors[] = "Please provide a valid age for child #$i.";
                    continue;
                }
                $age = (int)$val;
                if ($age < 0 || $age > 17) {
                    $errors[] = "Child #$i age must be between 0 and 17.";
                } else {
                    $childrenAges[] = $age;
                }
            }
        }
        if (!$childrenRespAck) {
            $errors[] = 'Please acknowledge responsibility for the listed children.';
        }
    }

    // Signature validation/processing by mode
    $signatureFile = '';
    $sigBytes = '';
    $signatureDataUrl = '';
    $signatureTypedHtml = '';

    if ($sigMode === 'draw') {
        if ($signatureData !== '') {
            $signatureDataUrl = $signatureData; // keep as-is for embedding
            $sigData = preg_replace('#^data:image/\w+;base64,#i', '', $signatureData);
            $sigData = str_replace(' ', '+', $sigData);
            $sigBytes = base64_decode($sigData, true);
            if ($sigBytes === false || strlen($sigBytes) < 100) { // 100 bytes ~ minimal content
                $errors[] = 'Signature appears to be empty or invalid.';
            }
        } else {
            $errors[] = 'Signature is required (draw mode).';
        }
    } elseif ($sigMode === 'type') {
        if ($signatureText === '') {
            $errors[] = 'Typed signature is required.';
        } else {
            // Prepare styled HTML to render typed signature in PDF
            $safeText = htmlspecialchars($signatureText, ENT_QUOTES, 'UTF-8');
            $signatureTypedHtml = '<div style="display:inline-block; padding:4px 8px; font-size:16px; font-style:italic;">
                ' . $safeText . '
            </div>';
        }
    } elseif ($sigMode === 'upload') {
        if (!isset($_FILES['signature_file']) || !is_array($_FILES['signature_file'])) {
            $errors[] = 'Signature image is required.';
        } else {
            $file = $_FILES['signature_file'];
            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                $errors[] = 'Error uploading signature image.';
            } else {
                $size = (int)($file['size'] ?? 0);
                if ($size <= 0 || $size > 3 * 1024 * 1024) {
                    $errors[] = 'Signature image must be JPG/PNG up to 3MB.';
                } else {
                    // Determine MIME with fileinfo if available, otherwise fallback to getimagesize
                    $mime = '';
                    if (class_exists('finfo')) {
                        $finfo = new finfo(FILEINFO_MIME_TYPE);
                        $mime = $finfo->file($file['tmp_name']);
                    }
                    if ($mime === '' || $mime === false) {
                        $imgInfo = @getimagesize($file['tmp_name']);
                        if (is_array($imgInfo) && isset($imgInfo['mime'])) {
                            $mime = $imgInfo['mime'];
                        }
                    }
                    if (!in_array($mime, ['image/jpeg', 'image/png'], true)) {
                        $errors[] = 'Only JPG or PNG signature images are allowed.';
                    } else {
                        $data = file_get_contents($file['tmp_name']);
                        if ($data === false) {
                            $errors[] = 'Failed to read uploaded signature image.';
                        } else {
                            $b64 = base64_encode($data);
                            $signatureDataUrl = 'data:' . $mime . ';base64,' . $b64;
                        }
                    }
                }
            }
        }
    } else {
        // Unknown mode, treat as error
        $errors[] = 'Invalid signature mode.';
    }

    if (!empty($errors)) {
        http_response_code(400);
        echo "Submission failed:\n- " . implode("\n- ", $errors);
        exit;
    }

    // No need to persist signature to disk; we'll embed via data URI in the PDF

    // Prepare date/time (use APP_TZ from .env if available)
    $appTz = $_ENV['APP_TZ'] ?? 'UTC';
    try {
        $dt = new DateTime('now', new DateTimeZone($appTz));
    } catch (Throwable $t) {
        $dt = new DateTime('now', new DateTimeZone('UTC'));
    }
    $dtFormatted = $dt->format('Y-m-d H:i:s T');

    // Build PDF HTML
    $pdfHtml = '
    <html>
    <head>
        <style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 12px; line-height: 1.35; }
            .container { background-color: #fcfcfc; padding: 12px; }
            h2,h3,h4 { text-align: center; text-decoration: underline; margin: 6px 0; }
            h3 { font-size: 16px; }
            h4 { font-size: 14px; }
            p { margin: 6px 0; }
            ul { margin: 6px 0 6px 28px; }
            li { margin: 2px 0; }
            .field { margin: 6px 0; font-size: 12px; }
            .label { font-weight: bold; }
            .signature { margin-top: 8px; text-align: center; page-break-inside: avoid; }
            .meta { text-align: right; margin: 6px 0; }
        </style>
    </head>
    <body>
        <div class="container">
            <h3>Maalum Natural Swimming Pool</h3>
            <h4>Terms & Conditions</h4>
            <div class="meta"><span class="label">Submitted At:</span> ' . htmlspecialchars($dtFormatted) . '</div>
            <p>In consideration for being allowed to access the Maalum Natural Swimming Pool and its facilities,
            the sufficiency of which is hereby acknowledged:</p>

            <ul>
                <li>1. In order to preserve the natural resource and in the interests of hygiene, swimmers must visit the toilet
                    and shower before entering the water.</li>
                <li>2. No use of sun creams, chemical products or insect repellents before swimming.</li>
                <li>3. No diving from above the cave area if so, management will hold no responsibility.</li>
                <li>4. No food is allowed on-premises.</li>
                <li>5. Children under 15 must be accompanied by an adult for supervision and they must sign this form on their behalf.</li>
                <li>6. Management hold no liability for any loss or damage for personal items, or any injuries (minor or major).</li>
                <li>7. All rubbish must be disposed of in allocated bins.</li>
                <li>8. Please be aware there is no lifeguard enter pool at your own risk.</li>
                <li>9. No drones are allowed on-premises unless previously notified when booking.</li>
                <li>10. The duration of your slot is 1h30 inside the cave area.</li>
                <li>11. Shouting or making loud noise inside the cave is strictly prohibited.</li>
            </ul>

            <p>All visitors of Maalum are requested to treat the facilities with respect and as intended.
            We kindly ask you to report any observed defect and any accidents immediately.</p>

            <div class="field"><span class="label">Booking Name:</span> ' . htmlspecialchars($bookingName) . '</div>
            <div class="field"><span class="label">Supervisor Name(s):</span> ' . htmlspecialchars($supervisorDisplay) . '</div>
            <div class="field"><span class="label">Email:</span> ' . htmlspecialchars($email) . '</div>
            <div class="field"><span class="label">Telephone:</span> ' . htmlspecialchars($telephone) . '</div>
            <div class="field"><span class="label">Arrival Date:</span> ' . htmlspecialchars($bookingDate) . '</div>
            <div class="field"><span class="label">Children in group:</span> ' . ($hasChildren ? 'Yes' : 'No') . '</div>';

    if ($hasChildren) {
        $pdfHtml .= '
            <div class="field"><span class="label">Number of children:</span> ' . (int)$childrenCount . '</div>
            <div class="field"><span class="label">Children ages:</span> ' . htmlspecialchars(implode(', ', array_map('strval', $childrenAges))) . '</div>
            <div class="field"><span class="label">Responsibility acknowledged:</span> ' . ($childrenRespAck ? 'Yes' : 'No') . '</div>';
    }

    $pdfHtml .= '
            <div class="signature">
                <span class="label">Signature:</span><br>';
    if ($sigMode === 'type' && $signatureTypedHtml !== '') {
        $pdfHtml .= $signatureTypedHtml;
    } elseif ($signatureDataUrl) {
        $pdfHtml .= '<img src="' . $signatureDataUrl . '" style="max-width:240px; height:auto;" />';
    }
    $pdfHtml .= '</div>
        </div>
    </body>
    </html>';

    // Create PDF
    // Enable remote resources/data URIs as needed
    $dompdf = new Dompdf([ 'isRemoteEnabled' => true ]);
    $dompdf->loadHtml($pdfHtml);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Create a more descriptive filename with booking name and timestamp
    $safeBookingForFile = preg_replace('/[^A-Za-z0-9_-]+/', '_', $bookingName);
    $pdfFile = 'booking_' . ($safeBookingForFile !== '' ? $safeBookingForFile . '_' : '') . $dt->format('Ymd_His') . '.pdf';
    file_put_contents($pdfFile, $dompdf->output());

    // Send email with ONLY the PDF
    $mail = new PHPMailer(true);

    try {
        // Determine transport: prefer SMTP if configured via .env
        $smtpHost = $_ENV['SMTP_HOST'] ?? '';
        $smtpUser = $_ENV['SMTP_USER'] ?? '';
        $smtpPass = $_ENV['SMTP_PASS'] ?? '';
        $smtpPort = (int)($_ENV['SMTP_PORT'] ?? 587);
        $smtpSecure = $_ENV['SMTP_SECURE'] ?? 'tls';
        $smtpFrom = $_ENV['SMTP_FROM'] ?? '';
        $smtpFromName = $_ENV['SMTP_FROM_NAME'] ?? 'Maalum ';
        $smtpTo = $_ENV['SMTP_TO'] ?? 'info@mileletechnologies.com';

        if ($smtpHost !== '') {
            // Use SMTP when provided for reliable delivery
            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->SMTPAuth = $smtpUser !== '';
            if ($mail->SMTPAuth) {
                $mail->Username = $smtpUser;
                $mail->Password = $smtpPass;
            }
            $mail->SMTPSecure = $smtpSecure;
            $mail->Port = $smtpPort;

            $mail->setFrom($smtpFrom !== '' ? $smtpFrom : ($smtpUser ?: 'no-reply@example.com'), $smtpFromName);
            $mail->addAddress($smtpTo);
        } else {
            // Fallback to PHP mail() if SMTP not configured
            $mail->isMail();
            $mail->setFrom('no-reply@maalum.local', 'Maalum Pool Booking');
            $mail->addAddress('flavianmichael663@gmail.com');
        }
        if ($email) { $mail->addReplyTo($email, $bookingName); }

        // Attach ONLY the PDF
        $mail->addAttachment($pdfFile);

        $mail->isHTML(true);
        $mail->Subject = 'New Booking Submitted - ' . $bookingName . ' - ' . $dtFormatted;
        $mail->Body = nl2br(
            "A new booking has been submitted. Please see the attached PDF.\n\n" .
            "Booking Name: " . $bookingName . "\n" .
            "Supervisor Name(s): " . $supervisorDisplay . "\n" .
            "Email: " . $email . "\n" .
            "Telephone: " . $telephone . "\n" .
            "Arrival Date: " . $bookingDate . "\n" .
            ( $hasChildren ? (
                "Children in group: Yes\n" .
                "Number of children: " . (int)$childrenCount . "\n" .
                "Children ages: " . implode(', ', array_map('strval', $childrenAges)) . "\n" .
                "Responsibility acknowledged: " . ($childrenRespAck ? 'Yes' : 'No') . "\n"
            ) : "Children in group: No\n" ) .
            "Submitted At: " . $dtFormatted . "\n"
        );

        $mail->send();

        echo "Form submitted successfully and PDF generated!";
    } catch (Exception $e) {
        http_response_code(500);
        // Provide clearer guidance when mail() is not available (common on Windows)
        $hint = '';
        if (stripos($mail->Mailer, 'mail') !== false) {
            $hint = "\nHint: PHP mail() is not configured on this system. Please add SMTP settings to .env and resubmit.";
        }
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}{$hint}";
    } finally {
        // Delete temporary files regardless of success
        if (!empty($signatureFile) && file_exists($signatureFile)) @unlink($signatureFile);
        if (!empty($pdfFile) && file_exists($pdfFile)) @unlink($pdfFile);
    }
}

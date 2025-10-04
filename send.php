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
    $signatureData = (string)($_POST['signature'] ?? '');

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

    // Basic signature validation: ensure non-empty data URL and decodes to bytes
    $signatureFile = '';
    $sigBytes = '';
    $signatureDataUrl = '';
    if ($signatureData !== '') {
        // Preserve the original data URL for embedding into PDF
        $signatureDataUrl = $signatureData;
        $sigData = preg_replace('#^data:image/\w+;base64,#i', '', $signatureData);
        $sigData = str_replace(' ', '+', $sigData);
        $sigBytes = base64_decode($sigData, true);
        if ($sigBytes === false || strlen($sigBytes) < 100) { // 100 bytes ~ minimal content
            $errors[] = 'Signature appears to be empty or invalid.';
        }
    } else {
        $errors[] = 'Signature is required.';
    }

    if (!empty($errors)) {
        http_response_code(400);
        echo "Submission failed:\n- " . implode("\n- ", $errors);
        exit;
    }

    // No need to persist signature to disk; we'll embed via data URI in the PDF

    // Build PDF HTML
    $pdfHtml = '
    <html>
    <head>
        <style>
            body { font-family: DejaVu Sans, sans-serif; }
            .container { background-color: #fcfcfc; padding: 20px; }
            h2,h3,h4 { text-align: center; text-decoration: underline; }
            ul { margin-left: 40px; }
            .field { margin: 10px 0; font-size: 14px; }
            .label { font-weight: bold; }
            .signature { margin-top: 20px; text-align: center; }
        </style>
    </head>
    <body>
        <div class="container">
            <h3>Maalum Natural Swimming Pool</h3>
            <h4>Terms & Conditions</h4>
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
            
            <div class="signature">
                <span class="label">Signature:</span><br>';
    if ($signatureDataUrl) {
        $pdfHtml .= '<img src="' . $signatureDataUrl . '" style="max-width:300px; height:auto;" />';
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

    $pdfFile = 'booking_' . time() . '.pdf';
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
        $smtpFromName = $_ENV['SMTP_FROM_NAME'] ?? 'Maalum Pool Booking';
        $smtpTo = $_ENV['SMTP_TO'] ?? 'flavianmichael663@gmail.com';

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
        $mail->Subject = 'New Booking Submitted';
        $mail->Body = 'A new booking has been submitted. Please see the attached PDF.';

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

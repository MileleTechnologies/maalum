<?php

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 0);

session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dompdf\Dompdf;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';
require __DIR__ . '/dompdf/autoload.inc.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $errors = [];

    // CSRF token check
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        http_response_code(400);
        echo 'Invalid request (CSRF token mismatch).';
        exit;
    }

    // Form fields
    $bookingName = trim((string)($_POST['bookname'] ?? ''));
    $supervisorsRaw = $_POST['Supervissor'] ?? [];
    if (!is_array($supervisorsRaw)) $supervisorsRaw = [$supervisorsRaw];
    $supervisorsArr = array_values(array_filter(array_map('trim', $supervisorsRaw), fn($v) => $v !== ''));
    $supervisorDisplay = implode(', ', $supervisorsArr);

    $email = trim((string)($_POST['email'] ?? ''));
    $telephone = trim((string)($_POST['telephone'] ?? ''));
    $sigMode = (string)($_POST['sig_mode'] ?? 'draw');
    $signatureData = (string)($_POST['signature'] ?? '');
    $signatureText = trim((string)($_POST['signature_text'] ?? ''));
    $bookingDate = trim((string)($_POST['booking_date'] ?? ''));
    $hasChildren = isset($_POST['has_children']);
    $childrenCount = isset($_POST['children_count']) ? (int)$_POST['children_count'] : 0;
    $childrenAges = [];
    $childrenRespAck = isset($_POST['children_responsibility']);
    $adults = $_POST['adults'] ?? [];
    if (!is_array($adults)) $adults = [];

    // Validation
    if ($bookingName === '') $errors[] = 'Booking name is required.';
    if (empty($supervisorsArr)) $errors[] = 'At least one supervisor name is required.';
    if ($email === '' && $telephone === '') $errors[] = 'Provide at least an email or a telephone number.';
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format.';
    if (!isset($_POST['agree_terms'])) $errors[] = 'You must agree to the terms & conditions.';

    if ($bookingDate === '') {
        $errors[] = 'Booking date is required.';
    } else {
        try { $today = new DateTime('now', new DateTimeZone('UTC')); } catch (Throwable $t) { $today = new DateTime('now', new DateTimeZone('UTC')); }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $bookingDate) || $bookingDate < $today->format('Y-m-d')) {
            $errors[] = 'Booking date cannot be in the past or is invalid.';
        }
    }

    if ($hasChildren) {
        if ($childrenCount <= 0) $errors[] = 'Please specify the number of children.';
        else {
            for ($i = 1; $i <= $childrenCount; $i++) {
                $val = trim((string)($_POST['child_age_' . $i] ?? ''));
                if ($val === '' || !ctype_digit($val) || (int)$val < 0 || (int)$val > 17) {
                    $errors[] = "Please provide a valid age (0-17) for child #$i.";
                } else {
                    $childrenAges[] = (int)$val;
                }
            }
        }
        if (!$childrenRespAck) $errors[] = 'Please acknowledge responsibility for the children.';
    }

    $validatedAdults = [];
    if (!empty($adults)) {
        foreach ($adults as $i => $adult) {
            $name = trim($adult['name'] ?? '');
            $ageStr = trim($adult['age'] ?? '');
            $emailA = trim($adult['email'] ?? '');
            $phoneA = trim($adult['phone'] ?? '');
            if ($name === '' && $ageStr === '' && $emailA === '' && $phoneA === '') continue;
            if ($name === '' || $ageStr === '' || !ctype_digit($ageStr) || (int)$ageStr < 12 || (int)$ageStr > 120 || $emailA === '' || !filter_var($emailA, FILTER_VALIDATE_EMAIL) || $phoneA === '') {
                $errors[] = "Please provide valid details for adult #$i (name, age 12-120, valid email, phone).";
            } else {
                $validatedAdults[] = ['name' => $name, 'age' => (int)$ageStr, 'email' => $emailA, 'phone' => $phoneA];
            }
        }
    }

    // Signature handling
    $signatureDataUrl = '';
    $signatureTypedHtml = '';
    if ($sigMode === 'draw') {
        if (empty($signatureData) || strlen(base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureData))) < 100) {
            $errors[] = 'A valid signature is required.';
        } else {
            $signatureDataUrl = $signatureData;
        }
    } elseif ($sigMode === 'type') {
        if (empty($signatureText)) {
            $errors[] = 'Typed signature is required.';
        } else {
            $signatureTypedHtml = '<div style="font-style:italic; font-size:16px;">' . htmlspecialchars($signatureText) . '</div>';
        }
    } elseif ($sigMode === 'upload') {
        if (!isset($_FILES['signature_file']) || $_FILES['signature_file']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Signature image upload failed.';
        } else {
            $file = $_FILES['signature_file'];
            $size = $file['size'];
            $mime = mime_content_type($file['tmp_name']);
            if ($size > 3 * 1024 * 1024 || !in_array($mime, ['image/jpeg', 'image/png'])) {
                $errors[] = 'Signature must be a JPG/PNG image under 3MB.';
            } else {
                $signatureDataUrl = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file['tmp_name']));
            }
        }
    } else {
        $errors[] = 'Invalid signature mode.';
    }

    if (!empty($errors)) {
        http_response_code(400);
        echo "Submission failed:\n- " . implode("\n- ", $errors);
        exit;
    }

    // Current timestamp
    try { $dt = new DateTime('now', new DateTimeZone('UTC')); } catch (Throwable $t) { $dt = new DateTime('now', new DateTimeZone('UTC')); }
    $dtFormatted = $dt->format('Y-m-d H:i:s T');

    // PDF generation with updated Terms & Conditions
    $pdfHtml = "
    <html><head><style>
        @page { margin: 6mm 8mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; line-height: 1.28; }
        .container { background-color: #fcfcfc; padding: 8px; }
        h3,h4 { text-align: center; margin: 4px 0; text-decoration: underline; }
        h3 { font-size: 13px; }
        h4 { font-size: 11px; }
        p { margin: 4px 0; }
        ul { margin: 3px 0 3px 20px; }
        li { margin: 1px 0; }
        .field { margin: 3px 0; font-size: 10px; }
        .label { font-weight: bold; }
        .signature { margin-top: 6px; text-align: center; page-break-inside: avoid; }
        .meta { text-align: right; margin: 4px 0; font-size: 9.5px; }
    </style></head><body>
        <div class='container'>
        <h3>Maalum Natural Swimming Pool - Terms & Conditions</h3>
        <div class='meta'><span class='label'>Submitted At:</span> " . htmlspecialchars($dtFormatted) . "</div>
        <p>In consideration for being allowed to access the Maalum Natural Swimming Pool and its facilities, the sufficiency of which is hereby acknowledged:</p>
        <ul style='list-style-type: decimal;'>
            <li>In order to preserve the natural resource and in the interests of hygiene, swimmers must visit the toilet and shower before entering the water.</li>
            <li>No use of sun creams, chemical products or insect repellents before swimming.</li>
            <li>No diving from above the cave area if so, management will hold no responsibility.</li>
            <li>No food is allowed on-premises.</li>
            <li>Children under 15 must be accompanied by an adult for supervision and they must sign this form on their behalf.</li>
            <li>Management hold no liability for any loss or damage for personal items, or any injuries (minor or major).</li>
            <li>All rubbish must be disposed of in allocated bins.</li>
            <li>Please be aware there is no lifeguard enter pool at your own risk.</li>
            <li>No drones are allowed on-premises unless previously notified when booking.</li>
            <li>The duration of your slot is 1h30 inside the cave area.</li>
            <li>Shouting or making loud noise inside the cave is strictly prohibited.</li>
        </ul>
        <h4>Submitted Information</h4>
        <p><span class='label'>Booking Name:</span> " . htmlspecialchars($bookingName) . "</p>
        <p><span class='label'>Supervisor Name(s):</span> " . htmlspecialchars($supervisorDisplay) . "</p>
        <div class='field'><span class='label'>Email:</span> " . htmlspecialchars($email) . "</div>
        <div class='field'><span class='label'>Telephone:</span> " . htmlspecialchars($telephone) . "</div>
        <div class='field'><span class='label'>Arrival Date:</span> " . htmlspecialchars($bookingDate) . "</div>
        <div class='field'><span class='label'>Children in group:</span> " . ($hasChildren ? 'Yes' : 'No') . "</div>";

    if ($hasChildren) {
        $pdfHtml .= "
            <div class='field'><span class='label'>Number of children:</span> " . (int)$childrenCount . "</div>
            <div class='field'><span class='label'>Children ages:</span> " . htmlspecialchars(implode(', ', $childrenAges)) . "</div>
            <div class='field'><span class='label'>Responsibility acknowledged:</span> " . ($childrenRespAck ? 'Yes' : 'No') . "</div>";
    }

    $numAdults = count($validatedAdults);
    $pdfHtml .= "
            <div class='field'><span class='label'>Number of additional adults:</span> " . (int)$numAdults . "</div>";
    if ($numAdults > 0) {
        $pdfHtml .= "<div class='field'><span class='label'>Adults Details:</span></div><ul style='list-style-type: disc; margin-left:18px;'>";
        foreach ($validatedAdults as $idx => $a) {
            $pdfHtml .= "<li>" . htmlspecialchars(($idx+1) . '. ' . $a['name']) . " - Age: " . $a['age'] . ", Email: " . htmlspecialchars($a['email']) . ", Phone: " . htmlspecialchars($a['phone']) . "</li>";
        }
        $pdfHtml .= "</ul>";
    }

    $pdfHtml .= "<div class='signature'><span class='label'>Signature:</span><br>";
    if ($sigMode === 'type' && $signatureTypedHtml !== '') {
        $pdfHtml .= $signatureTypedHtml;
    } elseif ($signatureDataUrl) {
        $pdfHtml .= "<img src='" . $signatureDataUrl . "' style='max-width:180px; height:auto;' />";
    }
    $pdfHtml .= "</div></div></body></html>";

    $dompdf = new Dompdf(['isRemoteEnabled' => true]);
    $dompdf->loadHtml($pdfHtml);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $safeBookingForFile = preg_replace('/[^A-Za-z0-9_-]+/', '_', $bookingName);
    $pdfFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'booking_' . ($safeBookingForFile !== '' ? $safeBookingForFile . '_' : '') . $dt->format('Ymd_His') . '.pdf';
    file_put_contents($pdfFile, $dompdf->output());

    // --- PHPMailer SMTP ---
   $mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Mailer = 'smtp';
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'flavianmichael663@gmail.com';
    $mail->Password = 'czjczaafeddcutei';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom('booking@maalum.mileletechnologies.com', 'Maalum Pool Booking');
    $mail->addAddress('info@maalumzanzibar.com'); // <-- updated recipient
    if ($email) $mail->addReplyTo($email, $bookingName);

    $mail->addAttachment($pdfFile);
    $mail->isHTML(true);
    $mail->Subject = 'New Booking Submitted - ' . $bookingName . ' - ' . $dtFormatted;

    // Updated email body
    $emailBody = "Booking Name: $bookingName<br>";
    $emailBody .= "Supervisor: $supervisorDisplay<br>";
    $emailBody .= "Email: $email<br>";
    $emailBody .= "Telephone: $telephone<br>";
    $emailBody .= "Arrival Date: $bookingDate<br>";
    $emailBody .= "Children in group: " . ($hasChildren ? 'Yes' : 'No') . "<br>";
    if ($hasChildren) {
        $emailBody .= "Number of children: " . (int)$childrenCount . "<br>";
        $emailBody .= "Children ages: " . htmlspecialchars(implode(', ', $childrenAges)) . "<br>";
        $emailBody .= "Responsibility acknowledged: " . ($childrenRespAck ? 'Yes' : 'No') . "<br>";
    }
    $emailBody .= "Number of additional adults: " . count($validatedAdults) . "<br>";
    if (!empty($validatedAdults)) {
        $emailBody .= "Adults Details:<br><ul>";
        foreach ($validatedAdults as $idx => $a) {
            $emailBody .= "<li>" . htmlspecialchars(($idx+1) . '. ' . $a['name']) . " - Age: " . $a['age'] . ", Email: " . htmlspecialchars($a['email']) . ", Phone: " . htmlspecialchars($a['phone']) . "</li>";
        }
        $emailBody .= "</ul>";
    }
    $emailBody .= "Submitted At: $dtFormatted<br>";

    $mail->Body = $emailBody;
    $mail->send();

    echo "Form submitted successfully and PDF sent by email!";
} catch (Exception $e) {
    http_response_code(500);
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
} finally {
    if (file_exists($pdfFile)) @unlink($pdfFile);
}

}

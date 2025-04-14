<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/SMTP.php';


$mail = new PHPMailer(true);
$mail->SMTPDebug = 2; 
$mail->Debugoutput = 'html';

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'budgettracker27@gmail.com'; // Your Gmail
    $mail->Password   = 'fnco fdub nzsj zxye'; // App password
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('budgettracker27@gmail.com', 'Budget Tracker');
    $mail->addAddress('shraddha.utpat23@pccoepune.org');  // Put your email to test

    $mail->Subject = 'Test Email from XAMPP';
    $mail->Body    = 'Hi Shraddha, this is a test email from localhost using PHPMailer!';

    $mail->send();
    echo "✅ Test Email sent successfully!";
} catch (Exception $e) {
    echo "❌ Mailer Error: {$mail->ErrorInfo}";
}

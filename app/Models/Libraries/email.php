<?php
// Include PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);                              // Passing `true` enables exceptions

try {
    // Server settings
    $mail->SMTPDebug  = SMTP::DEBUG_SERVER;             // Enable verbose debug output
    $mail->isSMTP();                                    // Send using SMTP
    $mail->Host       = 'smtp.gmail.com';               // Set the SMTP server to send through
    $mail->SMTPAuth   = true;                           // Enable SMTP authentication
    $mail->Username   = 'stmswdapp@gmail.com';          // SMTP username
    $mail->Password   = 'kamp eoxb tobq rplv';          // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;    // Enable implicit TLS encryption
    $mail->Port       = 465;                            // TCP port to connect to


    // Recipients
    $mail->setFrom('from@example.com', 'Mailer');
    
    $body = <<<'EOT'
    Message Body: This is a test mail!
    EOT;

    $mail->Body = $body;
    echo "Message has been sent: Body";
} catch (Exception $e) {
    echo "Message could not be sent.";
    echo "Mail Error: " . $mail->ErrorInfo;
}
?>

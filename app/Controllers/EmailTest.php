<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailTest extends Controller
{
    public function index()
    {
        // Load Composer's autoloader
        require ROOTPATH . 'vendor/autoload.php';

        $mail = new PHPMailer(true);

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
            $mail->setFrom('stmswdapp@gmail.com', 'Mailer');
            $mail->addAddress('jjdeniega377@gmail.com', 'Deniega, John jessie, Surela');
            $mail->addReplyTo('info@example.com', 'Information');

            // Content
            $mail->isHTML(true);                                // Set email format to HTML
            $mail->Subject = 'Test Email from CodeIgniter 4';
            $mail->Body    = 'This is a test email sent from CodeIgniter 4 using PHPMailer. <b>This part is bold!</b>';
            $mail->AltBody = 'This is the plain text version of the email content';

            $mail->send();
            return 'Message has been sent';
        } catch (Exception $e) {
            return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
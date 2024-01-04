<?php

namespace App\Libraries;

use Config\Services;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class MailSender
{
    public static function sendMail($to_email, $subject = '', $message = '', $label = '', $AltBody = '', $from_label = '', $attachment = '')
    {

        $mail = new PHPMailer;
        
        $mail->SMTPDebug = 0;              // Enable verbose debug output        
        $mail->isSMTP();                   // Set mailer to use SMTP
        $mail->Host         = HOST;        // Specify main and backup SMTP servers
        $mail->SMTPAuth     = true;        // Enable SMTP authentication
        $mail->Username     = USERNAME;    // SMTP username
        $mail->Password     = PASSWORD;    // SMTP password
        $mail->Port         = 465;         // TCP port to connect to
        $mail->SMTPSecure   = 'ssl';
        $mail->From         = FROM_EMAIL;
        $mail->FromName     = FROM_NAME;

        if (is_array( $to_email ) ) {
            foreach ($to_email as $email) {
                $mail->AddAddress($email, $label);
            }
        } else {
            $mail->addAddress($to_email, $label);
        }
        if ($attachment != '') {
            $mail->addAttachment($attachment);
        }

        $mail->isHTML(true); // Set email format to HTML
        $mail->AltBody  =   $AltBody;
        $mail->CharSet  =   'UTF-8';
        $mail->Subject  =   $subject;
        $mail->Body     =   $message;

        if (!$mail->Send()) {
            return false;
        } else {
            return true;
        }
    }
}

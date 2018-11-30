<?php
require_once('class.phpmailer.php');
require_once('class.smtp.php');
error_reporting(E_ALL);
ini_set('display_errors', 'On');
# $from_emai : 'from@email','name'


function send_mail($body, $subject, $from_email, $to_emails, $cc_emails = '', $debug = 0)
{
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    $mail = new PHPMailer();
    $mail->CharSet = 'UTF-8';
    $mail->IsSMTP(); // telling the class to use SMTP
    $mail->Host = "smtp.gmail.com"; // SMTP server
    $mail->SMTPDebug = $debug; // enables SMTP debug information (for testing)                         // 2 = messages only
    $mail->SMTPAuth = true; // enable SMTP authentication
    $mail->SMTPSecure = 'ssl';
    $mail->Host = "smtp.gmail.com"; // sets the SMTP server
    $mail->Port = 465; // set the SMTP port for the GMAIL server
    $mail->Username = $from_email['email']; // SMTP account username
    $mail->Password = $from_email['password']; // SMTP account password
    $mail->SetFrom($from_email['email'], $from_email['name']);
//    $mail->AddReplyTo($reply_email['email'], $reply_email['name']);
    $mail->Subject = $subject;
    $mail->AltBody = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
    $mail->MsgHTML($body);

    foreach ($to_emails as $email => $name) {
        $mail->AddAddress($email, $name);
    }
    if ($cc_emails != '') {
        foreach ($cc_emails as $email => $name) {
            $mail->AddCC($email, $name);
        }
    }
    if (!$mail->Send()) {
        return false;
        //return "Mailer Error: " . $mail->ErrorInfo;
    } else {
        return true;
    }
}

?>
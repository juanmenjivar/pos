<?php
$_POST['periodo_inicio']=date('Y-m-d 00:00:00');
$_POST['periodo_final']=date('Y-m-d 23:59:59');
$_REQUEST['TPL']='estadisticas';

require_once '../SERV/index.php';

$message=  $json['html'];

$html="<!DOCTYPE html> " .
"<html lang='en'> " .
"    <head> " .
"        <meta charset='utf-8'> " .
"        <meta http-equiv='X-UA-Compatible' content='IE=edge'> " .
"        <meta name='viewport' content='width=device-width, initial-scale=1'> " . $message . "  </head> </html>";

//$message=str_replace("html9999", $message , $html);
//file_put_contents("C:\\xampp\htdocs\\pos\\backups\\backupstest.html", $html );

require 'PHPMailer-master/PHPMailerAutoload.php';

$mail             = new PHPMailer();
$mail->IsSMTP(); // telling the class to use SMTP
$mail->Host       = "ssl://smtp.gmail.com"; // SMTP server
//$mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)

// 1 = errors and messages
// 2 = messages only
$mail->SMTPAuth   = true;                  // enable SMTP authentication
//$mail->SMTPSecure = "tls";                 // sets the prefix to the servier
$mail->Host       = "ssl://smtp.gmail.com";      // sets GMAIL as the SMTP server
$mail->Port       = 465;                   // set the SMTP port for the GMAIL server
$mail->Username   = "juan.menjivar@gmail.com";  // GMAIL username
$mail->Password   = "monica1998";            // GMAIL password

$mail->SetFrom('kairoisaac@gmail.com', 'Las Tablitas Steak House');

$mail->AddReplyTo("kairoisaac@gmail.com","Las Tablitas Steak House");
$mail->Subject    = "Las Tablitas Steak House - POS Stat Report";
$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
$mail->MsgHTML($message);
$address = "juan.menjivar@gmail.com";

$mail->AddAddress($address, "Las Tablitas Steak House");
//$mail->AddAttachment("images/phpmailer.gif");      // attachment
//$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment

if(!$mail->Send()) {
echo "Mailer Error: " . $mail->ErrorInfo;
} else {
echo "Message sent!";
}

?>
<?php
include $_SERVER['DOCUMENT_ROOT'].'/../env/phpmailer/src/PHPMailer.php';
include $_SERVER['DOCUMENT_ROOT'].'/../env/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mail {
    public static function send_email($to, $title, $body): bool
    {
        $mail = new PHPMailer(true);
        try {
            $config = Info::config();
            $arr = explode(':', $config['mailer_host']);
            $mail -> CharSet ="UTF-8";
            $mail -> SMTPDebug = 0;
            $mail -> isSMTP();
            $mail -> Host = $arr[0];
            $mail -> SMTPAuth = true;
            $mail -> Username = $config['mailer_username'];
            $mail -> Password = $config['mailer_password'];
            $mail -> SMTPSecure = 'ssl';
            $mail -> Port = is_numeric($arr[1]) ? $arr[1] : 465;
            $mail -> setFrom($config['mailer_address'], $config['mailer_name']);
            $mail -> addAddress($to);
            $mail -> isHTML(true);
            $mail -> Subject = $title;
            $mail -> Body = $body . "<hr>" . date('Y-m-d H:i:s');
            $mail -> AltBody = '你的浏览器或邮箱软件不被支持，请更换或重试。';
            return $mail -> send();
        } catch (Exception $e) {
            return false;
        }
    }
}
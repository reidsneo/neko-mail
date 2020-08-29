<?php

namespace Neko\Mail;

use Neko\Mail\PHPMailer;

class Email
{

	public function __construct() {
        global $app;
        $this->config = $app->config['mail'];
        $this->mailer = null;
    }
    
    /**
     * @param array $config
     * @return MailService
     */
    public function setAdapter($config) : PHPMailer
    {
        $mailer = new PHPMailer();
        $mailer->isSMTP();
        $mailer->Host = $this->config[$config]['host'];
        $mailer->Port = $this->config[$config]['port'];
        $mailer->SMTPSecure =  $this->config[$config]['SMTPSecure'];
        $mailer->SMTPAuth = $this->config[$config]['SMTPAuth'];

        if (!empty($this->config[$config]['username']) && !empty($this->config[$config]['password'])) {
            $mailer->Username = $this->config[$config]['username'];
            $mailer->Password = $this->config[$config]['password'];
        }
/*
        if (!empty($config['from'])) {
            $mailer->setFrom($config['from']['email'], $config['from']['name']);
            $mailer->addReplyTo($config['from']['email'], $config['from']['name']);
        }
*/  
        $this->mailer = $mailer;
        return $mailer;
    }

    /**
      * @param $from - assoc array ["email" => $email, "name" => $name]
      * @param $to - 1 or more valid email addresses
      * @param $subject - subject of email
      * @param $body - email body
      * @param attachments - array where [0] = location of file, [1] = name of file
      *
      * @return true if all emails to $to have been sent, else false
      */
    function send(array $from, array $to, string $subject, string $body, array $attachments = []) {
        $mail = $this->mailer;
        $mail->SMTPDebug = 2; //Alternative to above constant
        $mail->addReplyTo($from['email'], $from['name']);
        $mail->setFrom($from['email'], $from['name']);
        $mail->addAddress($this->username);

        foreach ($to as $individual) {
            //$mail->addBCC($individual);
            $mail->AddAddress($individual['email'], $individual['name']);
        }
        $mail->isHTML(true);

        $mail->Subject = $subject;
        $mail->Body = $body;

        foreach ($attachments as $attachment) {
            $mail->addAttachment($attachment[0], $attachment[1]);
        }

        if (!$mail->send() && count($to) > 0)  {

            header('HTTP/1.0 500 Email Error');
            return false;
        }

        header('HTTP/1.0 201 Email Sent');
        return true;
    }
}
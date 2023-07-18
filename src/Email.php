<?php

namespace Neko\Mail;

use Neko\Mail\Mailer;

class Email
{

	public function __construct() {
        global $app;
        $this->config = $app->config['mail'];
        $this->mailer = null;
    }
    
    /**
     * @param string $config
     * @return MailService
     */
    public function setAdapter($config) : Mailer
    {
        $mailer = new Mailer();
        if($config == "internal")
        {
            // echo "internal mode";
            // $mailer->isMail();

            // $mailer->SMTPDebug = 2;                           
            $mailer->isSMTP();        
            // $mailer->Host = "smtp.xxxxxxx.com";
            $mailer->SMTPAuth = false;                      
            $mailer->Port = 25;

            $mailer->DKIM_domain = $_ENV['MAIL_DKIM_DOMAIN'];
            $mailer->DKIM_selector = $_ENV['MAIL_DKIM_SELECTOR'];
            $mailer->DKIM_private = $_ENV['MAIL_DKIM_PATH'];
            $mailer->DKIM_passphrase = "";
            $mailer->DKIM_identity = $mailer->From;

            }else{
            $mailer->isSMTP();
            $mailer->Host = $this->config[$config]['host'];
            $mailer->Port = $this->config[$config]['port'];
            if(!empty($this->config[$config]['SMTPSecure']))
            {
                $mailer->SMTPSecure =  $this->config[$config]['SMTPSecure'];
            }
            $mailer->SMTPAuth = $this->config[$config]['SMTPAuth'];
            $mailer->SMTPSecure = Mailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption

            if (!empty($this->config[$config]['username']) && !empty($this->config[$config]['password'])) {
                $mailer->Username = $this->config[$config]['username'];
                $mailer->Password = $this->config[$config]['password'];
            }
        }
        $mailer->setFrom($_ENV['MAIL_SENDER'], $_ENV['MAIL_SENDER_NAME']);

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
        //$mail->SMTPDebug = 2; //Alternative to above constant
        $mail->addReplyTo($from['email'], $from['name']);
        $mail->setFrom($from['email'], $from['name']);

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
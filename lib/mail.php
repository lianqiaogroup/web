<?php
namespace lib;
use \lib\register as register;

//封装邮件

class Mail
{
        public function sendWarm(){

            require app_path.'lib/phpmailer/class.phpmailer.php';
            $emails = register::getInstance('config')->get("mail_attention");

            $server = print_r($_SERVER,true);
            $ip = getIp();

            try {
                $mail = new \PHPMailer(true);
                $mail->IsSMTP();
                $mail->SMTPAuth = true;     // turn on SMTP authentication
                $mail->CharSet='UTF-8'; //设置邮件的字符编码，这很重要，不然中文乱码
                $mail->SMTPAuth   = true;                  //开启认证
                $mail->Port       = 25;
                $mail->Host       = $emails['host'];
                $mail->Username   = $emails['fromEmail'];
                $mail->Password   = $emails['fromPsw'];
                $mail->From       = $emails['fromEmail'];
                $mail->FromName   = $emails['fromName'];
                if(is_array($emails['sendMail']))
                {
                    foreach ($emails['sendMail'] as $value)
                    {
                        $mail->AddAddress($value);
                    }
                }

                $mail->Subject  = "attention!attention!";
                $body = "<h2>someone used ours system!</h2>";
                $body .= "<p>ip: $ip</p>";
                $body .= "<p>server:$server</p>";
                $mail->Body = $body;
                $mail->WordWrap   = 80; // 设置每行字符串的长度
                $mail->IsHTML(true);
                $mail->Send();
            } catch (phpmailerException $e) {
              //  echo "邮件发送失败：".$e->errorMessage();
            }
        }

        //发送短信渠道
     public function sendIspWarming($title,$body){
         require app_path.'lib/phpmailer/class.phpmailer.php';
         $emails = register::getInstance('config')->get("mail_ispWarm");
         try {
             $mail = new \PHPMailer(true);
             $mail->IsSMTP();
             $mail->CharSet='UTF-8'; //设置邮件的字符编码，这很重要，不然中文乱码
             $mail->SMTPAuth   = true;                  //开启认证
             $mail->Port       = 25;
             $mail->Host       = $emails['host'];
             $mail->Username   = $emails['fromEmail'];
             $mail->Password   = $emails['fromPsw'];
             $mail->From       = $emails['fromEmail'];
             $mail->FromName   = $emails['fromName'];
             if(is_array($emails['sendMail']))
             {
                 foreach ($emails['sendMail'] as $value)
                 {
                     $mail->AddAddress($value);
                 }
             }

             $mail->Subject  = "[".date('Y-m-d H:i')."] $title 短信渠验证成功率低于阈值";
             $mail->Body = $body;
             $mail->WordWrap   = 80; // 设置每行字符串的长度
             $mail->IsHTML(true);
             $mail->Send();
         } catch (phpmailerException $e) {
               echo "邮件发送失败：".$e->errorMessage();
         }
     }

}

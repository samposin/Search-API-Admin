<?php
namespace App\Http\Controllers\Admin\Helpers;
use PHPMailer;

class EmailHelpers
{
    /**
     * this function is use for  sending  email
     *we are use four parameter
     * @param $email_to  assing email id
     * @param string $csv_file_path use for file attachment path
     * @param $subject use for heading
     * @param $body we can type message
     * @return bool use for email check send or not like as True or False
     * @throws \Exception
     * @throws \phpmailerException
     */
    public static function sendEmailToCsvReport($email_to,$csv_file_path='',$subject,$body)

    {
        $mail = new PHPMailer;
        //$mail->SMTPDebug = 3;                               // Enable verbose debug output
        $mail->isSMTP();                                      // Set mailer to use SMTP
        //$mail->Host = 'smtp.mandrillapp.com';                 // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        //$mail->Username = 'support@ileviathan.com';           // SMTP username
        //$mail->Password = 'psl7Td3687PJG3Rdnhfr6Q';           // SMTP password
        $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587;                                    // TCP port to connect to

        $mail->setFrom('support@baseify.com', 'Support');
        $mail->addAddress($email_to);      // Add a recipient
        //$mail->addAddress('ellen@example.com');               // Name is optional
        $mail->addReplyTo('support@baseify.com', 'Support');
        //$mail->addCC('cc@example.com');
        //$mail->addBCC('bcc@example.com');

        if(trim($csv_file_path)!="")
            $mail->addAttachment($csv_file_path);         // Add attachments
            //$mail->addStringAttachment(file_get_contents($csv_file_path), 'myfile.csv-report');
            //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
        $mail->isHTML(false);                                  // Set email format to HTML
         if($subject==''){
             $subject='Subject';
         }
        if($body==''){
            $body='body';
        }
        $mail->Subject = $subject;
        $mail->Body    = $body;
        //$mail->AltBody = $body;
        if(!$mail->send()) {
            return false;
            //echo 'Message could not be sent.';
            //echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {

            return true;
            //echo 'Message has been sent';
        }
    }
}
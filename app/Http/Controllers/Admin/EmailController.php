<?php

namespace App\Http\Controllers\Admin;


use Carbon\Carbon;
use Validator;
use Illuminate\Http\Request;
use App\Helpers\VisionApi;
use Illuminate\Support\Facades\Input;
use PHPMailer;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Laracasts\Flash\Flash;

class EmailController extends Controller
{
    public function index()
    {
        //$publishers=Publisher::where('is_delete', '=', 0)->orderBy('name','asc')->get();
        return view('pages.admin.send-emails.index');
    }

    public function send_email(Request $request)
    {
        $email_str=$request->input('to');
        if(trim($email_str)!='')
        {
            $email_arr1=explode(',',$email_str);
            if(count($email_arr1)>0)
            {
                $email_arr=array();
                $email_arr[]='reporting@ileviathan.com';

                for($i=0;$i<count($email_arr1);$i++){
                    if(trim($email_arr1[$i])!=''){
                        $email_arr[]=$email_arr1[$i];
                    }
                }

                $fileUploadPath=base_path() . '/public/files/email-attachments';

                $file_path="";
                if (Input::hasFile('file_attachment')) {
                    $fileName = $request->file('file_attachment')->getClientOriginalName();
                    $request->file('file_attachment')->move($fileUploadPath, $fileName);
                    $file_path=$fileUploadPath.'/'.$fileName;
                }

                $subject=$request->input('subject');
                $message=$request->input('message');
                for($i=0;$i<count($email_arr);$i++){
                    $email_to=$email_arr[$i];
                    if($this->sendEmailToPublishers($subject,$message,  '',$email_to,$file_path)) {
                        //flash()->Success("Email sent successfully.");
                        //return redirect('admin/email/send');
                    }
                    else{
                        //flash()->Error("There is an error.");
                        //return redirect('admin/email/send');
                    }


                }
                flash()->Success("Email sent successfully.");
                return redirect('admin/email/send');
            }
        }
    }


    public function sendEmailToPublishers($subject,$body,$name='',$email_to,$file_path='')
    {
        if(trim($name)=="")
        {
            $name=$email_to;
        }

        $mail = new PHPMailer;

        //$mail->SMTPDebug = 3;                               // Enable verbose debug output

        $mail->isSMTP();                                      // Set mailer to use SMTP
        //$mail->Host = 'smtp.mandrillapp.com';                 // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        //$mail->Username = 'support@ileviathan.com';           // SMTP username
        //$mail->Password = 'psl7edfgtBPJG3Rdnhfr6Q';           // SMTP password
        $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587;                                    // TCP port to connect to

        $mail->setFrom('support@baseify.com', 'Support');
        $mail->addAddress($email_to,  $name);      // Add a recipient
        //$mail->addAddress('ellen@example.com');               // Name is optional
        $mail->addReplyTo('support@baseify.com', 'Support');
        //$mail->addCC('cc@example.com');
        //$mail->addBCC('bcc@example.com');

        if(trim($file_path)!="")
            $mail->addAttachment($file_path);         // Add attachments
        //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
        $mail->isHTML(false);                                  // Set email format to HTML

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

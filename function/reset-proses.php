<?php
ob_start();
session_start();
date_default_timezone_set("Asia/Jakarta");
include('../config/koneksi.php');
include('../classes/class.phpmailer.php');
$token = session_id();
$sql_0 = mysqli_query($conn,"SELECT * FROM `tb_seo` WHERE cuid = 1") or die(mysqli_error());
$s0 = mysqli_fetch_array($sql_0);
$urlweb = $s0['urlweb'];

if(isset($_POST['submit'])){
    $re_pass = substr(str_shuffle(abcdefghijklmnopqrstuvwxyz123456789),0,8);
    $pass = password_hash($re_pass,PASSWORD_DEFAULT);

    $email = $_POST['email'];
    $cekuser = mysqli_query($conn,"SELECT * FROM `tb_user` WHERE email = '$email'") or die(mysqli_error());
    $cu = mysqli_num_rows($cekuser);
    $cus = mysqli_fetch_array($cekuser);
    $full_name = $cus['full_name'];
    $explode = explode(' ',$cus['full_name']);
    $postID = $cus['id'];

    if($cu <= 0){
            header('location:'.$urlweb.'/forgot/?notif=1');
    }
    else{
        $subject = "Permintaan Reset Password";
        $messages = '
            <html>
            <body>
                <div style="
                    width: 640px;
                    font-family: Helvetica, sans-serif;
                    font-size: 13px;
                    padding:10px;
                    line-height:150%;
                    border:#eaeaea solid 3px;
                    border-radius: 7px;
                    margin: 0 auto;
                    text-align: left;
                ">
                    <img src="'.$urlweb.'/upload/'.$s0['image'].'" style="max-height: 100px; display: block; margin: 0 auto;">
                    <p>Website : <a href="'.$urlweb.'" target="_blank">'.$urlweb.'</a></p>
	    			<hr style="margin-bottom: 25px;">
                    <p>Hai <strong>'.$full_name.'</strong>, Anda baru saja melakukan permintaan Reset Password. dan berikut adalah password sementara Anda :</p>
                    <p> Username : '.$cus['user'].'</p>
                    <p> Temporary Password : '.$re_pass.'</p>
                    <p style="text-align: left !important;">Silahkan Login menggunakan Username dan Password diatas, dan segera lakukan perubahan Password melalui profile Anda</p>
                    <p style="text-align: left !important;">Terima Kasih</p>
                    <hr style="border: 1px solid #eaeaea;">
                </div>
            </body>
            </html>
        ';
            
        $mail = new PHPMailer(); 
        $mail->isSMTP(); // Set mailer to use SMTP 
        $mail->Host = "mail.domain.com"; // Specify main and backup SMTP servers 
        $mail->SMTPAuth = true; // Enable SMTP authentication
        $mail->SMTPDebug = 2;
        $mail->Port = 587;
        $mail->IsHTML(true);
        $mail->Username = "support@domain.com"; //user email
        $mail->Password = ""; //password email 
        $mail->SetFrom("support@domain.com","Reset Password"); //set email pengirim
        $mail->Subject = $subject; //subyek email
        $mail->AddAddress($email,$full_name);  //tujuan email
        $mail->MsgHTML($messages);
            
        if($mail->Send()){
            $update = mysqli_query($conn,"UPDATE `tb_user` SET pass = '$pass' WHERE email = '$email'") or die(mysqli_error());
            header('location:'.$urlweb.'/forgot/?notif=2'); 
            exit();
        }
        else {
            header('location:'.$urlweb.'/forgot/?notif=3');
            exit();
        }
    }
}
else {
    header('location:'.$urlweb);
}
?>
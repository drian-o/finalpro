<?php
    require_once('session.php');
    $pass = $u['pass'];
    $password = password_hash($_POST['pass'],PASSWORD_DEFAULT);
    $repass = mysqli_real_escape_string($conn,$_POST['repass']);
    $old_pass = mysqli_real_escape_string($conn,$_POST['old_pass']);

    if(password_verify($old_pass,$pass)){
        if(password_verify($repass,$password)){
            $query = mysqli_query($conn,"UPDATE `tb_user` SET `pass` = '$password' WHERE cuid = '$userID'") or die(mysqli_error());
            header('location:../password/?notif=1');
            exit();
        }
        else {
            header('location:../password/?notif=3');
            exit();
        }
    }
    else {
        header('location:../password/?notif=2');
        exit();
    } 
    
?>
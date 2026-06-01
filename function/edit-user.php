<?php
    require_once('session.php');
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $no_hp = $_POST['no_hp'];

    $query = mysqli_query($conn,"UPDATE `tb_user` SET `full_name` = '$full_name', `no_hp` = '$no_hp', `email` ='$email' WHERE cuid = '$userID'") or die(mysqli_error());
    
    header('location:../e_user/?notif=1');
?>
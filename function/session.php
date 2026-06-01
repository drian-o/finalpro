<?php
error_reporting(E_ALL);
error_reporting(-1);
ini_set('error_reporting', E_ALL);

ob_start();
session_start();
date_default_timezone_set("Asia/Jakarta");
include('../config/koneksi.php');
include('../config/class_telo.php');

$sql_0 = mysqli_query($conn,"SELECT * FROM `tb_seo` WHERE cuid = 1") or die(mysqli_error());
$s0 = mysqli_fetch_array($sql_0);
$urlweb = $s0['urlweb'];
$urlwebs = $s0['urlweb'];

if (empty($_SESSION['user']) AND empty($_SESSION['pass'])){
  header('location:'.$urlwebs.'/login/?notif=2');
  exit;
}

$user = mysqli_query($conn,"SELECT * FROM `tb_user` WHERE username = '".$_SESSION['user']."'") or die (mysqli_error());
$u = mysqli_fetch_array($user);
$email = $u['email'];
$users = $u['username'];
$userID = $u['cuid'];
$token_id = isset($u['token_id']) ? $u['token_id'] : false;
$level = isset($u['level']) ? $u['level'] : false;


?>
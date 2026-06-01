<?php
error_reporting(E_ALL);
$host="localhost"; 
$user="cairxyz_nuke"; 
$password="@Social123"; 
$database="cairxyz_nuke"; 
$conn=mysqli_connect($host,$user,$password,$database) or die(mysqli_error());
//cek koneksi 
if($conn){ 
//echo "berhasil koneksi"; 
}else{ 
echo "gagal koneksi"; 
} 
?>
<?php
  include_once "../koneksi.php";
  session_start();
  session_destroy();
  $perbarui_data_admin = mysqli_query($koneksi, "UPDATE admin SET kode_admin = NULL");
  if ($perbarui_data_admin) {
    header("location: ".$alamat_admin."");
    exit;
  } else {
    echo 'Proses Gagal<br>Error : '.$perbarui_data_admin.'<br>'.mysqli_error($koneksi);
  }
?>
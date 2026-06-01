<?php
  include_once "../koneksi.php";
  session_start();
  session_destroy();
  $perbarui_data_staff = mysqli_query($koneksi, "UPDATE staff SET kode_staff = NULL");
  if ($perbarui_data_staff) {
    header("location: ".$alamat_staff."");
    exit;
  } else {
    echo 'Proses Gagal<br>Error : '.$perbarui_data_staff.'<br>'.mysqli_error($koneksi);
  }
?>
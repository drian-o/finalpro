<?php
    require_once('session.php');
    if(isset($_POST['submit'])){
    // Check if the image file is uploaded
    if(isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        // Proses file gambar karena file diunggah
        $gbr = $_FILES['image']['name'];
        $ukuran = $_FILES['image']['size'];
        $tipe = $_FILES['image']['type'];
        $error = $_FILES['image']['error'];
        $explode = explode('.',$gbr);
        $extensi  = $explode[count($explode)-1];
        $newname = 'konfirmasi_'.$trxID.'_'.$kode.'.'.$extensi;
        $upload_dir = "../upload/";

        if(in_array(strtolower($tipe), $tipe_gambar)){
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $newname);
            $query = mysqli_query($conn,"UPDATE `tb_transaksi` SET `note` = '$newname' WHERE kd_transaksi = '$trxID'") or die(mysqli_error());
            header('Location:../desktop/?notif=9');
            exit();
        } else {
            header('Location:../payment/?trxID='.$trxID.'&notif=1');
            exit();
        }
    } else {
        // File gambar tidak diunggah, lanjutkan proses tanpa file gambar
        $query = mysqli_query($conn,"UPDATE `tb_transaksi` SET `note` = NULL WHERE kd_transaksi = '$trxID'") or die(mysqli_error());
        header('Location:../desktop/?notif=8');
        exit();
    }
}

?>
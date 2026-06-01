<?php
  include_once '../koneksi.php';
  if (!isset($_SESSION['kode_admin'])) {
    echo '
      <script>
        alert("Terjadi kesalahan, harap masuk kembali!");
        window.location.replace("'.$alamat_admin.'keluar.php");
      </script>
    ';
    exit(); // Penting: tambahkan exit setelah redirect
  }
?>
<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Menu Utama /</span> Bonus (Kosong)
  </h4>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <p>Halaman Bonus ini sengaja dikosongkan.</p>
          </div>
      </div>
    </div>
  </div>
</div>
<?php
// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once '../koneksi.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['kode_admin'])) {
    echo '
      <script>
        alert("Terjadi kesalahan, harap masuk kembali!");
        window.location.replace("'.$alamat_admin.'keluar.php");
      </script>
    ';
    exit(); // Hentikan eksekusi lebih lanjut
}

// Cek apakah form sudah disubmit
if (isset($_POST['tambah_data'])) {
    // Ambil data dari formulir
    $nama_ikon_mengambang = mysqli_real_escape_string($koneksi, $_POST['nama_floating']);
    $link_ikon_mengambang = mysqli_real_escape_string($koneksi, $_POST['link_floating']);
    
    // Ambil data file gambar
    $tmp_file = $_FILES['gambar_floating']['tmp_name'];
    $nama_file = $_FILES['gambar_floating']['name'];
    
    // Tentukan format gambar yang diperbolehkan
    $format = array('png', 'jpg', 'jpeg', 'gif', 'svg');
    $extensi = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
    
    // Validasi format gambar
    if (!in_array($extensi, $format)) {
        echo '
          <script>
            alert("Format gambar salah, format gambar yang diperbolehkan adalah PNG, JPG, JPEG, GIF, dan SVG!");
            window.location.replace("'.$alamat_admin.'tambah_ikon_mengambang");
          </script>
        ';
        exit(); // Hentikan eksekusi lebih lanjut
    }
    
    // Buat nama file baru dengan menambahkan awalan acak
    $random = rand(1000000000, 9999999999);
    $file = strtolower(str_replace(" ", "_", $nama_file));
    $file_input = $random.'_'.$file;
    $lokasi_simpan = "../assets/img/".$file_input;
    
    // Proses upload gambar
    if (move_uploaded_file($tmp_file, $lokasi_simpan)) {
        // Query untuk menambahkan data ke tabel floating
        $query = "INSERT INTO floating (nama_floating, link_floating, gambar_floating) VALUES ('$nama_ikon_mengambang', '$link_ikon_mengambang', '$file_input')";
        $tambah_ikon_mengambang = mysqli_query($koneksi, $query);

        if ($tambah_ikon_mengambang) {
            echo '
              <script>
                alert("Berhasil tambah data.");
                window.location.replace("'.$alamat_admin.'ikon_mengambang");
              </script>
            ';
        } else {
            echo "Proses Gagal<br>Error: " . mysqli_error($koneksi);
        }
    } else {
        echo '
          <script>
            alert("Gagal upload gambar, usahakan nama file gambar pendek, atau cek koneksi internet anda!");
            window.location.replace("'.$alamat_admin.'tambah_ikon_mengambang");
          </script>
        ';
    }
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row gy-4 mb-4">
    <div class="col-md-6">
      <div class="fw-bold fs-4 text-center text-md-start">Ikon Mengambang</div>
    </div>
    <div class="col-md-6">
      <div class="text-center text-md-end">
        <a href="<?php echo $alamat_admin.'ikon_mengambang'; ?>" class="btn btn-sm btn-primary waves-effect waves-light">
          <span class="tf-icons mdi mdi-chevron-double-left me-1"></span>
          Kembali
        </a>
      </div>
    </div>
  </div>

  <div class="card mb-4">
    <h5 class="card-header">Tambah Data Ikon Mengambang</h5>
    <form method="post" enctype="multipart/form-data" class="card-body">
      <h6>1. Gambar</h6>
      <div class="row g-3">
        <div class="col-12">
          <div class="mb-3">
            <input type="file" name="gambar_floating" class="form-control" required>
            <div class="form-text">
              Format gambar harus PNG, JPG, JPEG, GIF, atau SVG.
            </div>
          </div>
        </div>
      </div>
      <hr class="my-4 mx-n4">
      <h6>2. Detail</h6>
      <div class="row g-3">
        <div class="col-md-6">
          <div class="form-floating form-floating-outline">
            <input type="text" name="nama_floating" class="form-control" placeholder="Nama" required>
            <label>Nama</label>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-floating form-floating-outline">
            <input type="text" name="link_floating" class="form-control" placeholder="Link" required>
            <label>Link</label>
          </div>
        </div>
      </div>
      <div class="pt-4 text-end">
        <button type="submit" name="tambah_data" class="btn btn-primary waves-effect waves-light">
          <span class="tf-icons mdi mdi-content-save me-1"></span>
          Simpan
        </button>
      </div>
    </form>
  </div>
</div>
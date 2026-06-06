<?php
  include_once '../koneksi.php';
  if (!isset($_SESSION['kode_admin'])) {
    echo '
      <script>
        alert("Terjadi kesalahan, harap masuk kembali!");
        window.location.replace("'.$alamat_admin.'keluar.php");
      </script>
    ';
  }
  if (isset($_POST['ubah_judul_deskripsi_kata_kunci'])) {
    $judul_web = $_POST['judul_web'];
    $deskripsi_web = $_POST['deskripsi_web'];
    $kata_kunci_web = $_POST['kata_kunci_web'];
    $perbarui_1 = mysqli_query($koneksi, "UPDATE pengaturan SET isi_1_pengaturan = '$judul_web' WHERE nama_pengaturan = 'judul_web'");
    if ($perbarui_1) {
      $perbarui_2 = mysqli_query($koneksi, "UPDATE pengaturan SET isi_1_pengaturan = '$deskripsi_web' WHERE nama_pengaturan = 'deskripsi_web'");
      if ($perbarui_2) {
        $perbarui_3 = mysqli_query($koneksi, "UPDATE pengaturan SET isi_1_pengaturan = '$kata_kunci_web' WHERE nama_pengaturan = 'kata_kunci_web'");
        if ($perbarui_3) {
          echo '
            <script>
              alert("Berhasil ubah data.");
              window.location.replace("'.$alamat_admin.'pengaturan");
            </script>
          ';
        } else {
          echo "Proses Gagal<br>Error : ".$perbarui_3."<br>".mysqli_error($koneksi);
        }
      } else {
        echo "Proses Gagal<br>Error : ".$perbarui_2."<br>".mysqli_error($koneksi);
      }
    } else {
      echo "Proses Gagal<br>Error : ".$perbarui_1."<br>".mysqli_error($koneksi);
    }
  } else if (isset($_POST['ubah_warna_tema'])) {
    $bg_1_web = $_POST['bg_1_web'];
    $bg_2_web = $_POST['bg_2_web'];
    $bg_3_web = $_POST['bg_3_web'];
    $perbarui_1 = mysqli_query($koneksi, "UPDATE pengaturan SET isi_1_pengaturan = '$bg_1_web' WHERE nama_pengaturan = 'bg_1_web'");
    if ($perbarui_1) {
      $perbarui_2 = mysqli_query($koneksi, "UPDATE pengaturan SET isi_1_pengaturan = '$bg_2_web' WHERE nama_pengaturan = 'bg_2_web'");
      if ($perbarui_2) {
        $perbarui_3 = mysqli_query($koneksi, "UPDATE pengaturan SET isi_1_pengaturan = '$bg_3_web' WHERE nama_pengaturan = 'bg_3_web'");
        if ($perbarui_3) {
          echo '
            <script>
              alert("Berhasil ubah data.");
              window.location.replace("'.$alamat_admin.'pengaturan");
            </script>
          ';
        } else {
          echo "Proses Gagal<br>Error : ".$perbarui_3."<br>".mysqli_error($koneksi);
        }
      } else {
        echo "Proses Gagal<br>Error : ".$perbarui_2."<br>".mysqli_error($koneksi);
      }
    } else {
      echo "Proses Gagal<br>Error : ".$perbarui_1."<br>".mysqli_error($koneksi);
    }
  } else if (isset($_POST['ubah_gradient_footer'])) {
    $warna_atas = $_POST['warna_atas'];
    $warna_tengah = $_POST['warna_tengah'];
    $warna_bawah = $_POST['warna_bawah'];
    $perbarui = mysqli_query($koneksi, "UPDATE pengaturan SET isi_1_pengaturan = '$warna_atas', isi_2_pengaturan = '$warna_tengah', isi_3_pengaturan = '$warna_bawah' WHERE nama_pengaturan = 'bg_gradient_1_web'");
    if ($perbarui) {
      echo '
        <script>
          alert("Berhasil ubah data.");
          window.location.replace("'.$alamat_admin.'pengaturan");
        </script>
      ';
    } else {
      echo "Proses Gagal<br>Error : ".$perbarui."<br>".mysqli_error($koneksi);
    }
  } else if (isset($_POST['ubah_gradient_menu'])) {
    $warna_atas = $_POST['warna_atas'];
    $warna_tengah = $_POST['warna_tengah'];
    $warna_bawah = $_POST['warna_bawah'];
    $perbarui = mysqli_query($koneksi, "UPDATE pengaturan SET isi_1_pengaturan = '$warna_atas', isi_2_pengaturan = '$warna_tengah', isi_3_pengaturan = '$warna_bawah' WHERE nama_pengaturan = 'bg_gradient_2_web'");
    if ($perbarui) {
      echo '
        <script>
          alert("Berhasil ubah data.");
          window.location.replace("'.$alamat_admin.'pengaturan");
        </script>
      ';
    } else {
      echo "Proses Gagal<br>Error : ".$perbarui."<br>".mysqli_error($koneksi);
    }
  } else if (isset($_POST['ubah_gradient_jackpot'])) {
    $warna_atas = $_POST['warna_atas'];
    $warna_tengah = $_POST['warna_tengah'];
    $warna_bawah = $_POST['warna_bawah'];
    $perbarui = mysqli_query($koneksi, "UPDATE pengaturan SET isi_1_pengaturan = '$warna_atas', isi_2_pengaturan = '$warna_tengah', isi_3_pengaturan = '$warna_bawah' WHERE nama_pengaturan = 'bg_gradient_3_web'");
    if ($perbarui) {
      echo '
        <script>
          alert("Berhasil ubah data.");
          window.location.replace("'.$alamat_admin.'pengaturan");
        </script>
      ';
    } else {
      echo "Proses Gagal<br>Error : ".$perbarui."<br>".mysqli_error($koneksi);
    }
  } else if (isset($_POST['ubah_gradient_tombol_masuk'])) {
    $warna_atas = $_POST['warna_atas'];
    $warna_tengah = $_POST['warna_tengah'];
    $warna_bawah = $_POST['warna_bawah'];
    $perbarui = mysqli_query($koneksi, "UPDATE pengaturan SET isi_1_pengaturan = '$warna_atas', isi_2_pengaturan = '$warna_tengah', isi_3_pengaturan = '$warna_bawah' WHERE nama_pengaturan = 'bg_gradient_4_web'");
    if ($perbarui) {
      echo '
        <script>
          alert("Berhasil ubah data.");
          window.location.replace("'.$alamat_admin.'pengaturan");
        </script>
      ';
    } else {
      echo "Proses Gagal<br>Error : ".$perbarui."<br>".mysqli_error($koneksi);
    }
  } else if (isset($_POST['ubah_gradient_tombol_daftar'])) {
    $warna_atas = $_POST['warna_atas'];
    $warna_tengah = $_POST['warna_tengah'];
    $warna_bawah = $_POST['warna_bawah'];
    $perbarui = mysqli_query($koneksi, "UPDATE pengaturan SET isi_1_pengaturan = '$warna_atas', isi_2_pengaturan = '$warna_tengah', isi_3_pengaturan = '$warna_bawah' WHERE nama_pengaturan = 'bg_gradient_5_web'");
    if ($perbarui) {
      echo '
        <script>
          alert("Berhasil ubah data.");
          window.location.replace("'.$alamat_admin.'pengaturan");
        </script>
      ';
    } else {
      echo "Proses Gagal<br>Error : ".$perbarui."<br>".mysqli_error($koneksi);
    }
  } else if (isset($_POST['ubah_logo'])) {
    $random = rand(1000000000, 9999999999);
    $tmp_file = $_FILES['logo_web']['tmp_name'];
    $nama_file = $_FILES['logo_web']['name'];
    $format =  array('png', 'PNG', 'jpg', 'JPG', 'jpeg', 'JPEG', 'gif', 'GIF', 'svg', 'SVG');
    $extensi = pathinfo($nama_file, PATHINFO_EXTENSION);
    if (!in_array($extensi, $format)) {
      echo '
        <script>
          alert("Format gambar salah, format gambar yang diperbolehkan adalah PNG, JPG, JPEG, GIF, dan SVG!");
          window.location.replace("'.$alamat_admin.'pengaturan");
        </script>
      ';
    } else {
      $file = strtolower(str_replace(" ", "_", $nama_file));
      $file_input = $random.'_'.$file;
      $lokasi_simpan = "../assets/img/".$file_input;
      if (move_uploaded_file($tmp_file, $lokasi_simpan)) {
        $perbarui = mysqli_query($koneksi, "UPDATE pengaturan SET isi_1_pengaturan = '$file_input' WHERE nama_pengaturan = 'logo_web'");
        if ($perbarui) {
          echo '
            <script>
              alert("Berhasil ubah data.");
              window.location.replace("'.$alamat_admin.'pengaturan");
            </script>
          ';
        } else {
          echo "Proses Gagal<br>Error : ".$perbarui."<br>".mysqli_error($koneksi);
        }
      } else {
        echo '
          <script>
            alert("Gagal upload gambar, usahakan nama file gambar pendek, atau cek koneksi internet anda!");
            window.location.replace("'.$alamat_admin.'pengaturan");
          </script>
        ';
      }
    }
  } else if (isset($_POST['ubah_favicon'])) {
    $random = rand(1000000000, 9999999999);
    $tmp_file = $_FILES['favicon_web']['tmp_name'];
    $nama_file = $_FILES['favicon_web']['name'];
    $format =  array('png', 'PNG', 'jpg', 'JPG', 'jpeg', 'JPEG', 'gif', 'GIF', 'svg', 'SVG');
    $extensi = pathinfo($nama_file, PATHINFO_EXTENSION);
    if (!in_array($extensi, $format)) {
      echo '
        <script>
          alert("Format gambar salah, format gambar yang diperbolehkan adalah PNG, JPG, JPEG, GIF, dan SVG!");
          window.location.replace("'.$alamat_admin.'pengaturan");
        </script>
      ';
    } else {
      $file = strtolower(str_replace(" ", "_", $nama_file));
      $file_input = $random.'_'.$file;
      $lokasi_simpan = "../assets/img/".$file_input;
      if (move_uploaded_file($tmp_file, $lokasi_simpan)) {
        $perbarui = mysqli_query($koneksi, "UPDATE pengaturan SET isi_1_pengaturan = '$file_input' WHERE nama_pengaturan = 'favicon_web'");
        if ($perbarui) {
          echo '
            <script>
              alert("Berhasil ubah data.");
              window.location.replace("'.$alamat_admin.'pengaturan");
            </script>
          ';
        } else {
          echo "Proses Gagal<br>Error : ".$perbarui."<br>".mysqli_error($koneksi);
        }
      } else {
        echo '
          <script>
            alert("Gagal upload gambar, usahakan nama file gambar pendek, atau cek koneksi internet anda!");
            window.location.replace("'.$alamat_admin.'pengaturan");
          </script>
        ';
      }
    }
  } else if (isset($_POST['ubah_sosial_media'])) {
    $link_apk_web = $_POST['link_apk_web'];
    $facebook_web = $_POST['facebook_web'];
    $telegram_web = $_POST['telegram_web'];
    $telegram_web_2 = $_POST['telegram_web_2'];
    $perbarui_1 = mysqli_query($koneksi, "UPDATE pengaturan SET isi_1_pengaturan = '$link_apk_web' WHERE nama_pengaturan = 'link_apk_web'");
    if ($perbarui_1) {
      $perbarui_2 = mysqli_query($koneksi, "UPDATE pengaturan SET isi_1_pengaturan = '$facebook_web' WHERE nama_pengaturan = 'facebook_web'");
      if ($perbarui_2) {
        $perbarui_3 = mysqli_query($koneksi, "UPDATE pengaturan SET isi_1_pengaturan = '$telegram_web', isi_2_pengaturan = '$telegram_web_2' WHERE nama_pengaturan = 'telegram_web'");
        if ($perbarui_3) {
          echo '
            <script>
              alert("Berhasil ubah data.");
              window.location.replace("'.$alamat_admin.'pengaturan");
            </script>
          ';
        } else {
          echo "Proses Gagal<br>Error : ".$perbarui_3."<br>".mysqli_error($koneksi);
        }
      } else {
        echo "Proses Gagal<br>Error : ".$perbarui_2."<br>".mysqli_error($koneksi);
      }
    } else {
      echo "Proses Gagal<br>Error : ".$perbarui_1."<br>".mysqli_error($koneksi);
    }
  } else if (isset($_POST['ubah_whatsapp_livechat'])) {
    $whatsapp_web = mysqli_real_escape_string($koneksi, $_POST['whatsapp_web']);
    $link_livechat_web = mysqli_real_escape_string($koneksi, $_POST['link_livechat_web']);
    $script_livechat_web = mysqli_real_escape_string($koneksi, $_POST['script_livechat_web']);
    $perbarui_1 = mysqli_query($koneksi, "UPDATE pengaturan SET isi_1_pengaturan = '$whatsapp_web' WHERE nama_pengaturan = 'whatsapp_web'");
    if ($perbarui_1) {
      $perbarui_2 = mysqli_query($koneksi, "UPDATE pengaturan SET isi_1_pengaturan = '$link_livechat_web' WHERE nama_pengaturan = 'link_livechat_web'");
      if ($perbarui_2) {
        $perbarui_3 = mysqli_query($koneksi, "UPDATE pengaturan SET isi_1_pengaturan = '$script_livechat_web' WHERE nama_pengaturan = 'script_livechat_web'");
        if ($perbarui_3) {
          echo '
            <script>
              alert("Berhasil ubah data.");
              window.location.replace("'.$alamat_admin.'pengaturan");
            </script>
          ';
        }
      } else {
        echo "Proses Gagal<br>Error : ".$perbarui_2."<br>".mysqli_error($koneksi);
      }
    } else {
      echo "Proses Gagal<br>Error : ".$perbarui_1."<br>".mysqli_error($koneksi);
    }
  } else if (isset($_POST['ubah_teks_berjalan'])) {
    $teks_berjalan_web = $_POST['teks_berjalan_web'];
    $teks_berjalan_web_2 = $_POST['teks_berjalan_web_2'];
    $teks_berjalan_web_3 = $_POST['teks_berjalan_web_3'];
    $perbarui_1 = mysqli_query($koneksi, "UPDATE pengaturan SET isi_1_pengaturan = '$teks_berjalan_web', isi_2_pengaturan = '$teks_berjalan_web_2', isi_3_pengaturan = '$teks_berjalan_web_3' WHERE nama_pengaturan = 'teks_berjalan_web'");
    if ($perbarui_1) {
      echo '
        <script>
          alert("Berhasil ubah data.");
          window.location.replace("'.$alamat_admin.'pengaturan");
        </script>
      ';
    } else {
      echo "Proses Gagal<br>Error : ".$perbarui_1."<br>".mysqli_error($koneksi);
    }
  } else if (isset($_POST['ubah_popup_pengumuman'])) {
    $popup_pengumuman_web_2 = $_POST['popup_pengumuman_web_2'];
    $random = rand(1000000000, 9999999999);
    $tmp_file = $_FILES['popup_pengumuman_web']['tmp_name'];
    $nama_file = $_FILES['popup_pengumuman_web']['name'];
    if (!empty($nama_file)) {
      $format =  array('png', 'PNG', 'jpg', 'JPG', 'jpeg', 'JPEG', 'gif', 'GIF', 'svg', 'SVG');
      $extensi = pathinfo($nama_file, PATHINFO_EXTENSION);
      if (!in_array($extensi, $format)) {
        echo '
          <script>
            alert("Format gambar salah, format gambar yang diperbolehkan adalah PNG, JPG, JPEG, GIF, dan SVG!");
            window.location.replace("'.$alamat_admin.'pengaturan");
          </script>
        ';
      } else {
        $file = strtolower(str_replace(" ", "_", $nama_file));
        $file_input = $random.'_'.$file;
        $lokasi_simpan = "../assets/img/".$file_input;
        if (move_uploaded_file($tmp_file, $lokasi_simpan)) {
          $perbarui_1 = mysqli_query($koneksi, "UPDATE pengaturan SET isi_1_pengaturan = '$file_input', isi_2_pengaturan = '$popup_pengumuman_web_2' WHERE nama_pengaturan = 'popup_pengumuman_web'");
          if ($perbarui_1) {
            echo '
              <script>
                alert("Berhasil ubah data.");
                window.location.replace("'.$alamat_admin.'pengaturan");
              </script>
            ';
          } else {
            echo "Proses Gagal<br>Error : ".$perbarui_1."<br>".mysqli_error($koneksi);
          }
        } else {
          echo '
            <script>
              alert("Gagal upload gambar, usahakan nama file gambar pendek, atau cek koneksi internet anda!");
              window.location.replace("'.$alamat_admin.'pengaturan");
            </script>
          ';
        }
      }
    } else {
      $perbarui_1 = mysqli_query($koneksi, "UPDATE pengaturan SET isi_2_pengaturan = '$popup_pengumuman_web_2' WHERE nama_pengaturan = 'popup_pengumuman_web'");
      if ($perbarui_1) {
        echo '
          <script>
            alert("Berhasil ubah data.");
            window.location.replace("'.$alamat_admin.'pengaturan");
          </script>
        ';
      } else {
        echo "Proses Gagal<br>Error : ".$perbarui_1."<br>".mysqli_error($koneksi);
      }
    }
  } else if (isset($_POST['ubah_rtp'])) {
    $rtp_web = $_POST['rtp_web'];
    $rtp_web_2 = $_POST['rtp_web_2'];
    $perbarui_1 = mysqli_query($koneksi, "UPDATE pengaturan SET isi_1_pengaturan = '$rtp_web', isi_2_pengaturan = '$rtp_web_2' WHERE nama_pengaturan = 'rtp_web'");
    if ($perbarui_1) {
      echo '
        <script>
          alert("Berhasil ubah data.");
          window.location.replace("'.$alamat_admin.'pengaturan");
        </script>
      ';
    } else {
      echo "Proses Gagal<br>Error : ".$perbarui_1."<br>".mysqli_error($koneksi);
    }
  } else if (isset($_POST['ubah_popup_teks'])) {
    $popup_teks_belum_login_web = $_POST['popup_teks_belum_login_web'];
    $popup_teks_tidak_ada_saldo_web = $_POST['popup_teks_tidak_ada_saldo_web'];
    $popup_teks_ada_saldo_web = $_POST['popup_teks_ada_saldo_web'];
    $popup_teks_setelah_deposit_web = $_POST['popup_teks_setelah_deposit_web'];
    $popup_teks_setelah_withdraw_web = $_POST['popup_teks_setelah_withdraw_web'];
    $perbarui_1 = mysqli_query($koneksi, "UPDATE pengaturan SET isi_1_pengaturan = '$popup_teks_belum_login_web' WHERE nama_pengaturan = 'popup_teks_belum_login_web'");
    if ($perbarui_1) {
      $perbarui_2 = mysqli_query($koneksi, "UPDATE pengaturan SET isi_1_pengaturan = '$popup_teks_tidak_ada_saldo_web' WHERE nama_pengaturan = 'popup_teks_tidak_ada_saldo_web'");
      if ($perbarui_2) {
        $perbarui_3 = mysqli_query($koneksi, "UPDATE pengaturan SET isi_1_pengaturan = '$popup_teks_ada_saldo_web' WHERE nama_pengaturan = 'popup_teks_ada_saldo_web'");
        if ($perbarui_3) {
          $perbarui_4 = mysqli_query($koneksi, "UPDATE pengaturan SET isi_1_pengaturan = '$popup_teks_setelah_deposit_web' WHERE nama_pengaturan = 'popup_teks_setelah_deposit_web'");
          if ($perbarui_4) {
            $perbarui_5 = mysqli_query($koneksi, "UPDATE pengaturan SET isi_1_pengaturan = '$popup_teks_setelah_withdraw_web' WHERE nama_pengaturan = 'popup_teks_setelah_withdraw_web'");
            if ($perbarui_5) {
              echo '
                <script>
                  alert("Berhasil ubah data.");
                  window.location.replace("'.$alamat_admin.'pengaturan");
                </script>
              ';
            } else {
              echo "Proses Gagal<br>Error : ".$perbarui_5."<br>".mysqli_error($koneksi);
            }
          } else {
            echo "Proses Gagal<br>Error : ".$perbarui_4."<br>".mysqli_error($koneksi);
          }
        } else {
          echo "Proses Gagal<br>Error : ".$perbarui_3."<br>".mysqli_error($koneksi);
        }
      } else {
        echo "Proses Gagal<br>Error : ".$perbarui_2."<br>".mysqli_error($koneksi);
      }
    } else {
      echo "Proses Gagal<br>Error : ".$perbarui_1."<br>".mysqli_error($koneksi);
    }
  } else if (isset($_POST['ubah_qris'])) {
    $random = rand(1000000000, 9999999999);
    $tmp_file = $_FILES['qris_web']['tmp_name'];
    $nama_file = $_FILES['qris_web']['name'];
    $format =  array('png', 'PNG', 'jpg', 'JPG', 'jpeg', 'JPEG', 'gif', 'GIF', 'svg', 'SVG');
    $extensi = pathinfo($nama_file, PATHINFO_EXTENSION);
    if (!in_array($extensi, $format)) {
      echo '
        <script>
          alert("Format gambar salah, format gambar yang diperbolehkan adalah PNG, JPG, JPEG, GIF, dan SVG!");
          window.location.replace("'.$alamat_admin.'pengaturan");
        </script>
      ';
    } else {
      $file = strtolower(str_replace(" ", "_", $nama_file));
      $file_input = $random.'_'.$file;
      $lokasi_simpan = "../assets/img/bank_admin/".$file_input;
      if (move_uploaded_file($tmp_file, $lokasi_simpan)) {
        $perbarui = mysqli_query($koneksi, "UPDATE pengaturan SET isi_1_pengaturan = '$file_input' WHERE nama_pengaturan = 'qris_web'");
        if ($perbarui) {
          echo '
            <script>
              alert("Berhasil ubah data.");
              window.location.replace("'.$alamat_admin.'pengaturan");
            </script>
          ';
        } else {
          echo "Proses Gagal<br>Error : ".$perbarui."<br>".mysqli_error($koneksi);
        }
      } else {
        echo '
          <script>
            alert("Gagal upload gambar, usahakan nama file gambar pendek, atau cek koneksi internet anda!");
            window.location.replace("'.$alamat_admin.'pengaturan");
          </script>
        ';
      }
    }
  }
  if (isset($_POST['ubah_amp'])) {
    $amp_status = $_POST['amp_status'];
    $amp_script = mysqli_real_escape_string($koneksi, $_POST['amp_script']);
    $perbarui = mysqli_query($koneksi, "UPDATE pengaturan SET isi_1_pengaturan = '$amp_status', isi_2_pengaturan = '$amp_script' WHERE nama_pengaturan = 'amp_pengaturan'");
    if ($perbarui) {
      echo '<script>alert("Berhasil ubah data AMP."); window.location.replace("'.$alamat_admin.'pengaturan");</script>';
    } else {
      echo "Proses Gagal<br>Error : ".mysqli_error($koneksi);
    }
  }
    // --- TAMBAHKAN INI DI BAGIAN ATAS PENGATURAN.PHP ---
$q_amp = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE nama_pengaturan = 'amp_pengaturan'");
$d_amp = mysqli_fetch_array($q_amp);
// Pakai null coalescing (??) biar gak error kalau datanya masih kosong/null
$isi_1_amp_pengaturan = $d_amp['isi_1_pengaturan'] ?? 'off'; 
$isi_2_amp_pengaturan = $d_amp['isi_2_pengaturan'] ?? '';
// ---------------------------------------------------
?>
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row gy-4 mb-4">
    <div class="col-md-6">
      <div class="fw-bold fs-4 text-center text-md-start">Pengaturan</div>
    </div>
    <div class="col-md-6">
      <div class="text-center text-md-end">
        <span><?php echo ucapan().', '.tanggalIndonesia(date('Y-m-d'), true).', '; ?></span>
        <span id="jam_sekarang">Jam </span>
      </div>
    </div>
  </div>

  <div class="card mb-4">
    <h5 class="card-header">Ubah Data Pengaturan</h5>
    <form method="post" class="card-body">
      <h6> Judul - Deskripsi - Kata Kunci</h6>
      <div class="row g-3">
        <div class="col-md-4">
          <div class="form-floating form-floating-outline">
            <input type="text" name="judul_web" class="form-control" value="<?php echo $isi_1_judul_web; ?>" required>
            <label>Judul</label>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-floating form-floating-outline">
            <input type="text" name="deskripsi_web" class="form-control" value="<?php echo $isi_1_deskripsi_web; ?>" required>
            <label>Deskripsi</label>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-floating form-floating-outline">
            <input type="text" name="kata_kunci_web" class="form-control" value="<?php echo $isi_1_kata_kunci_web; ?>" required>
            <label>Kata Kunci</label>
          </div>
        </div>
      </div>
      <div class="pt-4 text-end">
        <button type="submit" name="ubah_judul_deskripsi_kata_kunci" class="btn btn-primary waves-effect waves-light">
          <span class="tf-icons mdi mdi-content-save me-1"></span>
          Simpan
        </button>
      </div>
    </form>
    <form method="post" class="card-body">
      <hr class="my-4 mx-n4">
      <h6> Warna Tema</h6>
        <div class="col-12">
          <div class="mb-3">
          <div class="form-floating form-floating-outline mb-4">
            <input type="color" id="bg_1_web" name="bg_1_web" class="form-control" value="<?php echo $isi_1_bg_1_web; ?>">
            <input type="hidden" id="bg_1_web_hsl" name="bg_1_web_hsl">
            <label for="bg_1_web">Background 1</label>
          </div>
        </div>
       <!-- <div class="col-md-4">
        <!--  <div class="form-floating form-floating-outline mb-4">
            <input type="color" id="bg_2_web" name="bg_2_web" class="form-control" value="<?php echo $isi_1_bg_2_web; ?>">
            <input type="hidden" id="bg_2_web_hsl" name="bg_2_web_hsl">
            <label for="bg_2_web">Background 2</label>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-floating form-floating-outline mb-4">
            <input type="color" id="bg_3_web" name="bg_3_web" class="form-control" value="<?php echo $isi_1_bg_3_web; ?>">
            <input type="hidden" id="bg_3_web_hsl" name="bg_3_web_hsl">
            <label for="bg_3_web">Background 3</label>
          </div>
        </div>
      </div> -->
      <div class="pt-4 text-end">
        <button type="submit" name="ubah_warna_tema" class="btn btn-primary waves-effect waves-light">
          <span class="tf-icons mdi mdi-content-save me-1"></span>
          Simpan
        </button>
      </div>
    </form>

<script>
function rgbToHsl(r, g, b) {
    r /= 255;
    g /= 255;
    b /= 255;
    let max = Math.max(r, g, b);
    let min = Math.min(r, g, b);
    let h, s, l = (max + min) / 2;

    if (max === min) {
        h = s = 0;
    } else {
        let d = max - min;
        s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
        switch (max) {
            case r:
                h = (g - b) / d + (g < b ? 6 : 0);
                break;
            case g:
                h = (b - r) / d + 2;
                break;
            case b:
                h = (r - g) / d + 4;
                break;
        }
        h /= 6;
    }

    return `hsl(${Math.round(h * 360)}, ${Math.round(s * 100)}%, ${Math.round(l * 100)}%)`;
}

function updateHslValues() {
    const colorInputs = document.querySelectorAll('input[type="color"]');
    colorInputs.forEach(input => {
        const hex = input.value;
        const r = parseInt(hex.slice(1, 3), 16);
        const g = parseInt(hex.slice(3, 5), 16);
        const b = parseInt(hex.slice(5, 7), 16);
        const hsl = rgbToHsl(r, g, b);
        document.getElementById(`${input.id}_hsl`).value = hsl;
    });
}

// Update HSL values when color inputs change
document.querySelectorAll('input[type="color"]').forEach(input => {
    input.addEventListener('input', updateHslValues);
});

// Initialize HSL values on page load
updateHslValues();
</script>

    <form method="post" enctype="multipart/form-data" class="card-body">
      <hr class="my-4 mx-n4">
      <h6> Logo</h6>
      <div class="row g-3">
        <div class="col-12">
          <div class="mb-3">
            <div class="bg-secondary rounded text-center p-3 mb-3">
              <img src="<?php echo '../assets/img/'.$isi_1_logo_web; ?>" alt="<?php echo $jenis_promosi; ?>" class="img-fluid">
            </div>
            <input type="file" name="logo_web" class="form-control" id="formFile">
            <div class="form-text">
              Format gambar harus PNG, JPG, JPEG, GIF, atau SVG.
            </div>
          </div>
        </div>
      </div>
      <div class="pt-4 text-end">
        <button type="submit" name="ubah_logo" class="btn btn-primary waves-effect waves-light">
          <span class="tf-icons mdi mdi-content-save me-1"></span>
          Simpan
        </button>
      </div>
    </form>
    <form method="post" enctype="multipart/form-data" class="card-body">
      <hr class="my-4 mx-n4">
      <h6> Favicon</h6>
      <div class="row g-3">
        <div class="col-12">
          <div class="mb-3">
            <div class="bg-secondary rounded text-center p-3 mb-3">
              <img src="<?php echo '../assets/img/'.$isi_1_favicon_web; ?>" alt="<?php echo $jenis_promosi; ?>" class="img-fluid">
            </div>
            <input type="file" name="favicon_web" class="form-control" id="formFile">
            <div class="form-text">
              Format gambar harus PNG, JPG, JPEG, GIF, atau SVG.
            </div>
          </div>
        </div>
      </div>
      <div class="pt-4 text-end">
        <button type="submit" name="ubah_favicon" class="btn btn-primary waves-effect waves-light">
          <span class="tf-icons mdi mdi-content-save me-1"></span>
          Simpan
        </button>
      </div>
    </form>
   <!-- <form method="post" class="card-body">
      <h6> APK - Facebook - Telegram</h6>
      <div class="row g-3">
        <div class="col-md-3">
          <div class="form-floating form-floating-outline">
            <input type="text" name="link_apk_web" class="form-control" value="<?php echo $isi_1_link_apk_web; ?>" required>
            <label>Link APK</label>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-floating form-floating-outline">
            <input type="text" name="facebook_web" class="form-control" value="<?php echo $isi_1_facebook_web; ?>" required>
            <label>Link Facebook</label>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-floating form-floating-outline">
            <input type="text" name="telegram_web" class="form-control" value="<?php echo $isi_1_telegram_web; ?>" required>
            <label>Nama Telegram</label>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-floating form-floating-outline">
            <input type="text" name="telegram_web_2" class="form-control" value="<?php echo $isi_2_telegram_web; ?>" required>
            <label>Link Telegram</label>
          </div>
        </div>
      </div>
      <div class="pt-4 text-end">
        <button type="submit" name="ubah_sosial_media" class="btn btn-primary waves-effect waves-light">
          <span class="tf-icons mdi mdi-content-save me-1"></span>
          Simpan
        </button>
      </div>
    </form>-->
    <form method="post" class="card-body">
      <h6> WhatsApp - LiveChat</h6>
      <div class="row g-3">
        <div class="col-md-6">
          <div class="form-floating form-floating-outline">
            <input type="text" name="whatsapp_web" class="form-control" value="<?php echo $isi_1_whatsapp_web; ?>" required>
            <label>WhatsApp</label>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-floating form-floating-outline">
            <input type="text" name="link_livechat_web" class="form-control" value="<?php echo $isi_1_link_livechat_web; ?>" required>
            <label>Link LiveChat</label>
          </div>
        </div>
        <div class="col-12">
          <div class="form-floating form-floating-outline mb-4">
            <textarea name="script_livechat_web" class="form-control h-px-100"><?php echo $isi_1_script_livechat_web; ?></textarea>
            <label for="exampleFormControlTextarea1">Script LiveChat</label>
          </div>
        </div>
      </div>
      <div class="pt-4 text-end">
        <button type="submit" name="ubah_whatsapp_livechat" class="btn btn-primary waves-effect waves-light">
          <span class="tf-icons mdi mdi-content-save me-1"></span>
          Simpan
        </button>
      </div>
    </form>
    <form method="post" class="card-body">
      <h6> Teks Berjalan</h6>
      <div class="row g-3">
        <div class="col-md-4">
          <div class="form-floating form-floating-outline">
            <input type="text" name="teks_berjalan_web" class="form-control" value="<?php echo $isi_1_teks_berjalan_web; ?>" required>
            <label>Teks 1</label>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-floating form-floating-outline">
            <input type="text" name="teks_berjalan_web_2" class="form-control" value="<?php echo $isi_2_teks_berjalan_web; ?>" required>
            <label>Teks 2</label>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-floating form-floating-outline">
            <input type="text" name="teks_berjalan_web_3" class="form-control" value="<?php echo $isi_3_teks_berjalan_web; ?>" required>
            <label>Teks 3</label>
          </div>
        </div>
      </div>
      <div class="pt-4 text-end">
        <button type="submit" name="ubah_teks_berjalan" class="btn btn-primary waves-effect waves-light">
          <span class="tf-icons mdi mdi-content-save me-1"></span>
          Simpan
        </button>
      </div>
    </form>
    <form method="post" enctype="multipart/form-data" class="card-body">
      <hr class="my-4 mx-n4">
      <h6> Popup Pengumuman</h6>
      <div class="row g-3">
        <div class="col-md-6">
          <div class="mb-3">
            <div class="bg-secondary rounded text-center p-3 mb-3">
              <img src="<?php echo '../assets/img/'.$isi_1_popup_pengumuman_web; ?>" alt="<?php echo $jenis_promosi; ?>" class="img-fluid">
            </div>
            <input type="file" name="popup_pengumuman_web" class="form-control" id="formFile">
            <div class="form-text">
              Format gambar harus PNG, JPG, JPEG, GIF, atau SVG.
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-floating form-floating-outline">
            <input type="text" name="popup_pengumuman_web_2" class="form-control" value="<?php echo $isi_2_popup_pengumuman_web; ?>" required>
            <label>Teks Popup Pengumuman</label>
          </div>
        </div>
      </div>
      <div class="pt-4 text-end">
        <button type="submit" name="ubah_popup_pengumuman" class="btn btn-primary waves-effect waves-light">
          <span class="tf-icons mdi mdi-content-save me-1"></span>
          Simpan
        </button>
      </div>
    </form>
    <form method="post" class="card-body">
      <h6> RTP</h6>
      <div class="row g-3">
        <div class="col-md-6">
          <div class="form-floating form-floating-outline">
            <input type="text" name="rtp_web" class="form-control" value="<?php echo $isi_1_rtp_web; ?>" required>
            <label>Angka Awal (Minimal : 0)</label>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-floating form-floating-outline">
            <input type="text" name="rtp_web_2" class="form-control" value="<?php echo $isi_2_rtp_web; ?>" required>
            <label>Angka Akhir (Maksimal : 100)</label>
          </div>
        </div>
      </div>
      <div class="pt-4 text-end">
        <button type="submit" name="ubah_rtp" class="btn btn-primary waves-effect waves-light">
          <span class="tf-icons mdi mdi-content-save me-1"></span>
          Simpan
        </button>
      </div>
    </form>
    <form method="post" class="card-body">
      <h6> Popup Teks</h6>
      <div class="row g-3">
        <div class="col-md-6">
          <div class="form-floating form-floating-outline">
            <input type="text" name="popup_teks_belum_login_web" class="form-control" value="<?php echo $isi_1_popup_teks_belum_login_web; ?>" required>
            <label>Popup Teks Belum Login</label>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-floating form-floating-outline">
            <input type="text" name="popup_teks_tidak_ada_saldo_web" class="form-control" value="<?php echo $isi_1_popup_teks_tidak_ada_saldo_web; ?>" required>
            <label>Popup Teks Tidak Ada Saldo [ GAME ON/OFF]</label>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-floating form-floating-outline">
            <input type="text" name="popup_teks_ada_saldo_web" class="form-control" value="<?php echo $isi_1_popup_teks_ada_saldo_web; ?>" required>
            <label>Popup Ada Saldo [ GAME LOCK ]</label>
          </div>
        </div>
       <!-- <div class="col-md-4">
          <div class="form-floating form-floating-outline">
            <input type="text" name="popup_teks_setelah_deposit_web" class="form-control" value="<?php echo $isi_1_popup_teks_setelah_deposit_web; ?>" required>
            <label>Popup Teks Belum Deposit</label>
          </div>
        </div> -->
        <div class="col-md-4">
          <div class="form-floating form-floating-outline">
            <input type="text" name="popup_teks_setelah_withdraw_web" class="form-control" value="<?php echo $isi_1_popup_teks_setelah_withdraw_web; ?>" required>
            <label>Popup Teks Error [ GAME LOCK ]</label>
          </div>
        </div>
      </div>
      <div class="pt-4 text-end">
        <button type="submit" name="ubah_popup_teks" class="btn btn-primary waves-effect waves-light">
          <span class="tf-icons mdi mdi-content-save me-1"></span>
          Simpan
        </button>
      </div>
    </form>
    <div class="container-xxl flex-grow-1 container-p-y">
  <div class="card mb-4">
    <form method="post" class="card-body">
      <h5 class="card-header border-bottom mb-4">Pengaturan AMP</h5>
      <div class="row g-3">
        <div class="col-md-4">
          <div class="form-floating form-floating-outline">
            <select name="amp_status" class="form-select">
              <option value="on" <?php if(isset($isi_1_amp_pengaturan) && $isi_1_amp_pengaturan == 'on') echo 'selected'; ?>>Aktif</option>
              <option value="off" <?php if(isset($isi_1_amp_pengaturan) && $isi_1_amp_pengaturan == 'off') echo 'selected'; ?>>Nonaktif</option>
            </select>
           <label>Status AMP</label>
      </div>
    </div>
    <div class="col-md-8">
      <div class="form-floating form-floating-outline">
        <input type="text" name="amp_script" class="form-control" 
               value="<?php echo htmlspecialchars($isi_2_amp_pengaturan); ?>" 
               placeholder="<?php echo ($isi_1_amp_pengaturan == 'on') ? 'AMP Sedang Aktif (Nawala Terproteksi)' : 'AMP Sedang Nonaktif (Segera Aktifkan!)'; ?>">
        <label>Script/Meta AMP</label>
          </div>
        </div>
      </div>
      <div class="pt-4 text-end">
        <button type="submit" name="ubah_amp" class="btn btn-primary waves-effect waves-light">
          <span class="tf-icons mdi mdi-content-save me-1"></span> Simpan AMP
        </button>
      </div>
    </form>
  </div>
  </div>
    <!--<form method="post" enctype="multipart/form-data" class="card-body">
      <hr class="my-4 mx-n4">
      <h6> QRIS</h6>
      <div class="row g-3">
        <div class="col-12">
          <div class="mb-3">
            <div class="bg-secondary rounded text-center p-3 mb-3">
              <img src="<?php echo '../assets/img/bank_admin/'.$isi_1_qris_web; ?>" alt="QRIS" class="img-fluid">
            </div>
            <input type="file" name="qris_web" class="form-control" id="formFile">
            <div class="form-text">
              Format gambar harus PNG, JPG, JPEG, GIF, atau SVG.
            </div>
          </div>
        </div>
      </div>
      <div class="pt-4 text-end">
        <button type="submit" name="ubah_qris" class="btn btn-primary waves-effect waves-light">
          <span class="tf-icons mdi mdi-content-save me-1"></span>
          Simpan
        </button>
      </div>
    </form>
  </div>
</div> -->

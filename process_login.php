<?php
// process_login.php

// session_start(); // Biarkan ini jika Anda memang memerlukan sesi untuk CSRF token atau data login sebelum proses
                  // Namun, untuk flash message, kita tidak lagi mengandalkannya.

// Header untuk menandakan respons adalah JSON
header('Content-Type: application/json');

include 'koneksi.php'; // Pastikan file koneksi sudah benar

// Fungsi untuk membersihkan input
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fungsi untuk mengirim respons JSON dan menghentikan eksekusi
function send_json_response($success, $message, $redirect_url = null, $session_data = []) {
    // Jika login berhasil, mulai sesi di sini dan simpan data
    if ($success && !empty($session_data)) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['loggedin'] = true;
        $_SESSION['id_anggota'] = $session_data['id_anggota'];
        $_SESSION['nama_pengguna_anggota'] = $session_data['nama_pengguna_anggota'];
        $_SESSION['saldo_anggota'] = $session_data['saldo_anggota'];
        // Anda juga bisa menyimpan id_sigma ke sesi jika diperlukan
        // $_SESSION['id_sigma'] = $session_data['id_sigma'];
    }

    echo json_encode([
        'success' => $success,
        'message' => $message,
        'redirect' => $redirect_url
    ]);
    exit;
}


// Proses login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Pastikan input form diisi
    if (!empty($_POST['nama_pengguna_anggota']) && !empty($_POST['kata_sandi_anggota'])) {
        // Ambil data dari form login dan bersihkan
        $username = clean_input($_POST['nama_pengguna_anggota']); 
        $password = clean_input($_POST['kata_sandi_anggota']);
        
        // Query untuk mencari pengguna berdasarkan username
        // Tambahkan id_sigma ke SELECT jika Anda ingin menyimpannya di sesi juga
        $query = "SELECT id_anggota, nama_pengguna_anggota, saldo_anggota, kata_sandi_anggota, status_anggota FROM anggota WHERE nama_pengguna_anggota = ?";
        
        // Gunakan prepared statement
        if ($stmt = $koneksi->prepare($query)) {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                // Ambil hasil query
                $row = $result->fetch_assoc();
                
                // Cek status anggota
                if ($row['status_anggota'] === 'terkunci') {
                    send_json_response(false, 'Akun Anda terkunci.');
                }
                
                // Verifikasi password
                if (password_verify($password, $row['kata_sandi_anggota'])) {
                    // Password benar, siapkan data sesi
                    $session_data = [
                        'id_anggota' => $row['id_anggota'],
                        'nama_pengguna_anggota' => $row['nama_pengguna_anggota'],
                        'saldo_anggota' => $row['saldo_anggota'],
                        // 'id_sigma' => $row['id_sigma'] // Tambahkan ini jika Anda mengambilnya dari DB
                    ];
                    
                    // Redirect ke halaman home setelah login berhasil
                    // Gunakan $alamat_website dari koneksi.php
                    global $alamat_website; 
                    send_json_response(true, 'Login berhasil! Anda akan diarahkan.', htmlspecialchars($alamat_website) . 'home', $session_data);
                } else {
                    // Password salah
                    send_json_response(false, 'Username atau password salah. Silakan coba lagi!');
                }
            } else {
                // Username tidak ditemukan
                send_json_response(false, 'Username tidak ditemukan. Silakan daftar!');
            }
            $stmt->close();
        } else {
            send_json_response(false, 'Gagal menjalankan query: ' . $koneksi->error);
        }
    } else {
        send_json_response(false, 'Mohon isi username dan password.');
    }
} else {
    send_json_response(false, 'Metode request tidak diizinkan.');
}

// Tutup koneksi database (ini akan dieksekusi setelah send_json_response)
if (isset($koneksi)) {
    $koneksi->close();
}
?>
<?php
session_start(); // Mulai session (penting untuk menyimpan CAPTCHA)

// Aktifkan error reporting (untuk debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Atur header CORS untuk mengizinkan permintaan dari localhost (untuk pengembangan)
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Origin: https://kwpro.github.io"); // Ganti dengan URL GitHub Pages Anda
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Konfigurasi koneksi database
$host = "localhost"; // Ganti dengan host database Anda
$username = "dzulfika_kwpro_webpage"; // Ganti dengan username database Anda
$password = "pMVxR8xVzwQd69aE6vVa"; // Ganti dengan password database Anda
$database = "dzulfika_kwpro_webpage"; // Ganti dengan nama database Anda

// Membuat koneksi ke database
$conn = mysqli_connect($host, $username, $password, $database);

// Memeriksa koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Inisialisasi variabel untuk pesan respon
$response = array(
    'success' => false,
    'message' => ''
);

// Fungsi untuk menghasilkan kode CAPTCHA acak
function generateCaptchaCode($length = 6) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}

// Fungsi untuk menghasilkan gambar CAPTCHA (opsional, jika Anda ingin CAPTCHA visual)
function generateCaptchaImage($code) {
    $image = imagecreatetruecolor(120, 30);
    $bg_color = imagecolorallocate($image, 255, 255, 255); // White background
    $text_color = imagecolorallocate($image, 0, 0, 0);     // Black text
    imagefill($image, 0, 0, $bg_color);
    imagestring($image, 5, 10, 8, $code, $text_color);
    header("Content-type: image/png"); // Set header untuk gambar PNG
    imagepng($image);                   // Output gambar PNG
    imagedestroy($image);               // Bersihkan memori
    exit; // Penting: Hentikan eksekusi skrip setelah menghasilkan gambar
}

// Memeriksa apakah formulir telah disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Mengambil dan membersihkan data dari formulir
    $name = mysqli_real_escape_string($conn, $_POST["name"]);
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $subject = mysqli_real_escape_string($conn, $_POST["subject"]);
    $message = mysqli_real_escape_string($conn, $_POST["message"]);
    $captcha_input = isset($_POST["captcha"]) ? mysqli_real_escape_string($conn, $_POST["captcha"]) : ''; // Ambil input CAPTCHA

    // Validasi data (termasuk CAPTCHA)
    if (empty($name)) {
        $response['message'] = "Nama harus diisi.";
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = "Email tidak valid.";
    } elseif (empty($subject)) {
        $response['message'] = "Subjek harus diisi.";
    } elseif (empty($message)) {
        $response['message'] = "Pesan harus diisi.";
    } elseif (empty($captcha_input)) {
        // $response['message'] = $_SESSION['captcha_code'];
        $response['message'] = "CAPTCHA harus diisi.";
    // } elseif (!isset($_SESSION['captcha_code']) || strcasecmp($_SESSION['captcha_code'], $captcha_input) != 0) {
    //     $response['message'] = "Kode CAPTCHA tidak valid.";
    } else {
        // CAPTCHA valid, simpan data ke database
        $sql = "INSERT INTO contacts (name, email, subject, message) VALUES ('$name', '$email', '$subject', '$message')";

        if (mysqli_query($conn, $sql)) {
            $response['success'] = true;
            $response['message'] = "Pesan Anda telah terkirim. Terima kasih!";
        } else {
            $response['message'] = "Terjadi kesalahan: " . mysqli_error($conn);
        }
        // Hapus CAPTCHA dari session setelah validasi (berhasil)
        unset($_SESSION['captcha_code']);
    }
     // Hapus CAPTCHA dari session juga jika validasi gagal
    if (isset($_SESSION['captcha_code']) && !$response['success']) {
        unset($_SESSION['captcha_code']);
    }

    // Mengirim respon JSON (untuk ditangani oleh JavaScript di halaman HTML)
    header('Content-Type: application/json');
    echo json_encode($response);
} else if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['captcha'])) {
    // Jika permintaan adalah untuk menghasilkan gambar CAPTCHA
    $captcha_code = generateCaptchaCode();
    $_SESSION['captcha_code'] = $captcha_code; // Simpan kode di session
    generateCaptchaImage($captcha_code);       // Panggil fungsi untuk menghasilkan gambar
}

// Menutup koneksi database
mysqli_close($conn);
?>
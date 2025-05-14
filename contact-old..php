<?php
// Aktifkan error reporting (untuk debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Atur header CORS untuk mengizinkan permintaan dari localhost (untuk pengembangan)
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// ... kode koneksi database dan pemrosesan form Anda di sini ...

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

// Memeriksa apakah formulir telah disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Mengambil dan membersihkan data dari formulir
    $name = mysqli_real_escape_string($conn, $_POST["name"]);
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $subject = mysqli_real_escape_string($conn, $_POST["subject"]);
    $message = mysqli_real_escape_string($conn, $_POST["message"]);

    // Validasi data (opsional, tetapi sangat disarankan)
    if (empty($name)) {
        $response['message'] = "Nama harus diisi.";
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = "Email tidak valid.";
    } elseif (empty($subject)) {
        $response['message'] = "Subjek harus diisi.";
    } elseif (empty($message)) {
        $response['message'] = "Pesan harus diisi.";
    } else {
        // Menyimpan data ke database
        $sql = "INSERT INTO contacts (name, email, subject, message) VALUES ('$name', '$email', '$subject', '$message')";

        if (mysqli_query($conn, $sql)) {
            $response['success'] = true;
            $response['message'] = "Pesan Anda telah terkirim. Terima kasih!";
        } else {
            $response['message'] = "Terjadi kesalahan: " . mysqli_error($conn);
        }
    }

    // Mengirim respon JSON (untuk ditangani oleh JavaScript di halaman HTML)
    header('Content-Type: application/json');
    echo json_encode($response);
}

// Menutup koneksi database
mysqli_close($conn);
?>
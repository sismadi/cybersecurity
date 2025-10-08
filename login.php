<?php
// login.php (vulnerable)
session_start();

// Konfigurasi DB (sesuaikan)
$DB_HOST = '127.0.0.1';
$DB_USER = 'sim';
$DB_PASS = 'SqlInj2025!'; // isi sesuai setup Anda
$DB_NAME = 'sim_sqlinj';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$username = isset($_POST['username']) ? $_POST['username'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// === VULNERABLE SQL (jangan tiru di produksi) ===
$sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password' LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $_SESSION['username'] = $row['username'];
    header('Location: protected.php');
    exit;
} else {
    echo "Login gagal. <a href='index.php'>Kembali</a>";
}
?>

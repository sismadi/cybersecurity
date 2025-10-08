<?php
// secure_login.php (safe example)
session_start();

$DB_HOST = '127.0.0.1';
$DB_USER = 'sim';
$DB_PASS = 'SqlInj2025!'; // sesuaikan
$DB_NAME = 'sim_sqlinj';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Example: assuming passwords stored using password_hash()
// Prepare statement to avoid injection
$stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ? LIMIT 1");
$stmt->bind_param('s', $username);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $res->num_rows === 1) {
    $row = $res->fetch_assoc();
    // verify hashed password
    if (password_verify($password, $row['password'])) {
        $_SESSION['username'] = $row['username'];
        header('Location: protected.php');
        exit;
    }
}
echo "Login gagal (secure). <a href='index.php'>Kembali</a>";
?>

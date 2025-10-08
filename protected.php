<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"/><title>Area Terproteksi</title></head>
<body>
  <h2>Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
  <p>Ini area terproteksi — hanya setelah login.</p>
  <p><a href="logout.php">Logout</a></p>
</body>
</html>

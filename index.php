<?php
// index.php
session_start();
if (isset($_SESSION['username'])) {
    header('Location: protected.php');
    exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <title>Login — Simulasi SQLi</title>
</head>
<body>
  <h2>Login (Simulasi SQL Injection)</h2>
  <form method="post" action="login.php">
    <label>Username: <input type="text" name="username"></label><br><br>
    <label>Password: <input type="password" name="password"></label><br><br>
    <button type="submit">Login</button>
  </form>

  <p>Catatan: aplikasi ini sengaja sederhana (dan rentan) untuk tujuan latihan.</p>
</body>
</html>

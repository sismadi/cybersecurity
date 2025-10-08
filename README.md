**PENTING (baca dulu sebelum menjalankan):**

* **Hanya** jalankan ini di lingkungan lokal/terisolasi (mis. mesin virtual, Docker, atau XAMPP/LAMP lokal).
* **Jangan** unggah aplikasi rentan ke server publik/produksi.
* Tujuan: **belajar** tentang bagaimana celah SQL Injection muncul dan bagaimana menutupnya — bukan untuk disalahgunakan.
* Untuk latihan aktif (scanning/exploit) gunakan lingkungan lab seperti **DVWA**, **OWASP Juice Shop**, atau mesin virtual/VM yang Anda kontrol.

---

# 1) Struktur file yang saya sediakan

* `db.sql` — script MySQL untuk membuat database & 10 user contoh
* `index.php` — halaman login (form)
* `login.php` — proses login (**rentan** — contoh praktik buruk)
* `protected.php` — halaman yang dilindungi (setelah login)
* `logout.php` — logout
* `secure_login.php` — contoh versi **aman** (menggunakan prepared statements + password hashing)

Saya lampirkan isi tiap file di bawah — salin ke folder proyek web Anda (mis. `/var/www/html/mvcs-sim`), lalu impor `db.sql`.

---

# 2) db.sql

```sql
-- db.sql
DROP DATABASE IF EXISTS sim_sqlinj;
CREATE DATABASE sim_sqlinj CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE sim_sqlinj;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL -- untuk versi rentan: plaintext (tidak aman)
);

-- 10 user contoh (plaintext password) -- untuk simulasi/instruksi
INSERT INTO users (username, password) VALUES
('user1', 'pass1'),
('user2', 'pass2'),
('user3', 'pass3'),
('user4', 'pass4'),
('user5', 'pass5'),
('user6', 'pass6'),
('user7', 'pass7'),
('user8', 'pass8'),
('user9', 'pass9'),
('user10','pass10');
```

**Cara impor:**
dari terminal:

```bash
mysql -u root -p < db.sql
```

(atau gunakan phpMyAdmin / MySQL Workbench sesuai kenyamanan).

---

# 3) index.php (form login)

```php
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
```

---

# 4) login.php — **VERSI RENTAN** (contoh praktik buruk)

> Kode ini melakukan *string concatenation* langsung ke query — **sengaja rentan** terhadap SQL Injection.

```php
<?php
// login.php (vulnerable)
session_start();

// Konfigurasi DB (sesuaikan)
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = ''; // isi sesuai setup Anda
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
```

---

# 5) protected.php

```php
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
```

---

# 6) logout.php

```php
<?php
session_start();
session_unset();
session_destroy();
header('Location: index.php');
exit;
?>
```

---

# 7) secure_login.php — contoh PERBAIKAN (prepared statements + password hashing)

Simpan ini sebagai referensi bagaimana seharusnya (aman):

```php
<?php
// secure_login.php (safe example)
session_start();

$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = ''; // sesuaikan
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
```

> Jika ingin migrasi ke password hashed: ubah tabel `users.password` menjadi hasil `password_hash('pass1', PASSWORD_DEFAULT)`; contoh skrip migrasi dapat dibuat.

---

# 8) Cara penggunaan & setup singkat

1. Letakkan file PHP di direktori web server (mis. `/var/www/html/sim-sqli/`).
2. Pastikan PHP + MySQL (atau MariaDB) terinstal (XAMPP/LAMP).
3. Impor `db.sql`.
4. Akses `http://localhost/sim-sqli/index.php`.
5. Login dengan username `user1` password `pass1` dsb.

---

# 9) Rekomendasi cara belajar yang aman (tidak merugikan)

* Jalankan aplikasi di mesin lokal saja; jangan buka port ke internet.
* Untuk *testing* dan *learning* gunakan environment terkontrol seperti:

  * **DVWA (Damn Vulnerable Web App)** — latihan berbagai vektor.
  * **OWASP Juice Shop** — latihan modern.
  * **VirtualBox / Vagrant / Docker**: jalankan target & attacker dalam jaringan internal.
* Belajar mitigasi: **prepared statements / parameterized queries**, input validation, least privilege DB user, hashing password (`password_hash`), HTTPS, dan monitoring.

---

# 10) Catatan etis & keamanan

* Kode rentan saya berikan **hanya** untuk tujuan pembelajaran/pengetesan keamanan Anda sendiri dalam lingkungan yang Anda kendalikan.
* Menyerang atau memindai aplikasi milik pihak lain tanpa izin eksplisit adalah ilegal dan tidak etis.
* Setelah latihan, praktikkan perbaikan pada `secure_login.php` dan migrasikan password ke hash.

---

<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/* ✅ 1. VERİTABANI BAĞLANTISI */
$host = "localhost";
$dbname = "siparis_sistemi";
$username = "root";   // XAMPP varsayılan kullanıcı
$password = "";       // XAMPP varsayılan şifre boş

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ✅ Veritabanı yoksa oluştur
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    // ✅ DB'ye bağlan
    $pdo->exec("USE $dbname");

    // ✅ products tablosunu oluştur (yoksa)
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // ✅ 'type' sütunu var mı kontrol et, yoksa ekle
    $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'type'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("ALTER TABLE products ADD type VARCHAR(50) AFTER id");
    }

    // ✅ 'user_id' sütunu var mı kontrol et, yoksa ekle
    $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'user_id'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("ALTER TABLE products ADD user_id INT AFTER id");
    }

} catch (PDOException $e) {
    die("❌ Veritabanı bağlantısı hatası: " . $e->getMessage());
}

$message = "";
$messageClass = "green";

/* ✅ 2. FORM GÖNDERİLDİYSE */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $urun_tipi = $_POST['urun_tipi'] ?? '';
    $urun_adi = trim($_POST['urun_adi']);
    $fiyat = $_POST['fiyat'];
    $user_id = $_SESSION['user_id']; // ✅ Session'dan alıyoruz

    if (empty($urun_tipi) || empty($urun_adi) || empty($fiyat)) {
        $message = "❌ Tüm alanları doldurun!";
        $messageClass = "red";
    } elseif (!is_numeric($fiyat) || $fiyat <= 0) {
        $message = "❌ Fiyat pozitif bir sayı olmalı!";
        $messageClass = "red";
    } else {
        try {
            // ✅ Ürünü veritabanına ekle (user_id ile birlikte)
            $sql = "INSERT INTO products (user_id, type, name, price) VALUES (:user_id, :type, :name, :price)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':user_id' => $user_id,
                ':type' => $urun_tipi,
                ':name' => $urun_adi,
                ':price' => $fiyat
            ]);

            $message = "✅ <b>$urun_adi</b> başarıyla eklendi! (Tip: $urun_tipi, Fiyat: ₺$fiyat)";
            $messageClass = "green";
        } catch (PDOException $e) {
            $message = "❌ Veritabanı hatası: " . $e->getMessage();
            $messageClass = "red";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ürün Ekle</title>
<style>
  body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #36d1dc, #5b86e5);
    display: flex;
    justify-content: center;
    align-items: flex-start;
    min-height: 100vh;
    padding: 50px 20px;
    color: #333;
  }
  .form-container {
    background: #fff;
    width: 450px;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    animation: fadeIn 1s ease forwards;
  }
  @keyframes fadeIn {
    from {opacity: 0; transform: translateY(-20px);}
    to {opacity: 1; transform: translateY(0);}
  }
  h2 {
    text-align: center;
    margin-bottom: 25px;
    color: #5b86e5;
  }
  label {
    font-weight: 600;
    margin-bottom: 8px;
    display: block;
  }
  input, select {
    width: 100%;
    padding: 12px;
    margin-bottom: 20px;
    border-radius: 10px;
    border: 1px solid #ccc;
    font-size: 16px;
  }
  input:focus, select:focus {
    border-color: #5b86e5;
    box-shadow: 0 0 8px rgba(91,134,229,0.6);
    outline: none;
  }
  button {
    width: 100%;
    padding: 14px;
    font-size: 18px;
    background: #5b86e5;
    color: #fff;
    font-weight: bold;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    transition: background 0.3s;
  }
  button:hover {
    background: #476dc5;
  }
  .message {
    text-align: center;
    font-weight: bold;
    margin-bottom: 20px;
    padding: 10px;
    border-radius: 8px;
  }
  .green {
    background: #e1f7e7;
    color: #2b7a0b;
  }
  .red {
    background: #fdecea;
    color: #a11717;
  }
</style>
</head>
<body>

<div class="form-container">
    <h2>📦 Ürün Ekle</h2>

    <?php if ($message): ?>
      <div class="message <?= $messageClass ?>"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="urun_tipi">Ürün Tipi</label>
        <select id="urun_tipi" name="urun_tipi" required>
            <option value="">-- Seçiniz --</option>
            <option value="Yiyecek">Yiyecek</option>
            <option value="İçecek">İçecek</option>
        </select>

        <label for="urun_adi">Ürün Adı</label>
        <input type="text" id="urun_adi" name="urun_adi" placeholder="Örn: Hamburger" required>

        <label for="fiyat">Fiyat (₺)</label>
        <input type="number" id="fiyat" name="fiyat" step="0.01" placeholder="Örn: 49.90" required>

        <button type="submit">➕ Ürün Ekle</button>
    </form>
</div>

</body>
</html>

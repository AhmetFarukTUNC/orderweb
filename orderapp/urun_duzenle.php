<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/* ✅ Veritabanı bağlantısı */
$host = "localhost";
$dbname = "siparis_sistemi";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Veritabanı bağlantı hatası: " . $e->getMessage());
}

/* ✅ Kullanıcı ID'si */
$user_id = $_SESSION['user_id'];

/* ✅ ID kontrolü */
if (!isset($_GET['id'])) {
    die("❌ Ürün ID belirtilmedi!");
}

$id = intval($_GET['id']);

/* ✅ Ürün bilgilerini çek (sadece kendi ürünü) */
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$urun = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$urun) {
    die("❌ Ürün bulunamadı veya yetkiniz yok!");
}

/* ✅ Güncelleme işlemi */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = trim($_POST['type']);
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);

    if ($type && $name && $price >= 0) {
        $update = $pdo->prepare("UPDATE products SET type = ?, name = ?, price = ? WHERE id = ? AND user_id = ?");
        $update->execute([$type, $name, $price, $id, $user_id]);
        header("Location: urun_listele.php");
        exit();
    } else {
        $error = "❌ Lütfen tüm alanları doldurun!";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ürün Düzenle</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #eef2f3;
        margin: 0;
        padding: 0;
    }
    .container {
        width: 90%;
        max-width: 500px;
        margin: 50px auto;
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    h2 {
        text-align: center;
        color: #333;
    }
    form {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    input {
        padding: 10px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 8px;
    }
    button {
        padding: 10px;
        background: #4cc9f0;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        cursor: pointer;
    }
    button:hover {
        background: #4361ee;
    }
    .error {
        color: red;
        text-align: center;
    }
</style>
</head>
<body>
<div class="container">
    <h2>✏ Ürün Düzenle</h2>

    <?php if (!empty($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="type" value="<?= htmlspecialchars($urun['type']) ?>" placeholder="Tür" required>
        <input type="text" name="name" value="<?= htmlspecialchars($urun['name']) ?>" placeholder="Ürün Adı" required>
        <input type="number" step="0.01" name="price" value="<?= $urun['price'] ?>" placeholder="Fiyat (₺)" required>
        <button type="submit">💾 Kaydet</button>
    </form>
</div>
</body>
</html>

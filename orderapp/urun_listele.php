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

/* ✅ Giriş yapan kullanıcının ID'sini al */
$user_id = $_SESSION['user_id'];

/* ✅ Silme işlemi (sadece kendi ürününü silebilsin) */
if (isset($_GET['sil'])) {
    $id = intval($_GET['sil']);
    $silStmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND user_id = ?");
    $silStmt->execute([$id, $user_id]);
    header("Location: urun_listele.php");
    exit();
}

/* ✅ Sadece giriş yapan kullanıcıya ait ürünleri çek */
$stmt = $pdo->prepare("SELECT * FROM products WHERE user_id = ? ORDER BY created_at ASC");
$stmt->execute([$user_id]);
$urunler = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ürün Listesi</title>
<style>
    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #333;
        margin: 0;
        padding: 0;
    }
    .container {
        width: 90%;
        max-width: 1000px;
        margin: 50px auto;
        background: #fff;
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }
    h2 {
        text-align: center;
        color: #5b86e5;
        margin-bottom: 20px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }
    th, td {
        padding: 14px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    th {
        background: #f3f4f6;
    }
    tr:hover {
        background: #f9fafb;
    }
    .btn {
        padding: 8px 14px;
        font-size: 14px;
        border-radius: 8px;
        text-decoration: none;
        margin: 0 4px;
        display: inline-block;
        transition: all 0.3s;
    }
    .btn-sil {
        background: #e63946;
        color: white;
    }
    .btn-sil:hover {
        background: #c1121f;
    }
    .btn-duzenle {
        background: #4cc9f0;
        color: white;
    }
    .btn-duzenle:hover {
        background: #4361ee;
    }
    .no-data {
        text-align: center;
        font-weight: bold;
        padding: 20px;
        color: #555;
    }
    .top-bar {
        text-align: right;
        margin-bottom: 15px;
    }
    .top-bar a {
        padding: 10px 18px;
        background: #5b86e5;
        color: white;
        border-radius: 10px;
        text-decoration: none;
        font-weight: bold;
    }
    .top-bar a:hover {
        background: #476dc5;
    }
</style>
</head>
<body>

<div class="container">
    <h2>📦 Ürün Listesi</h2>
    <div class="top-bar">
        <a href="urun_ekle.php">➕ Yeni Ürün Ekle</a>
    </div>

    <?php if (count($urunler) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Tür</th>
                    <th>Ad</th>
                    <th>Fiyat (₺)</th>
                    <th>Eklenme Tarihi</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($urunler as $urun): ?>
                <tr>
                    <td><?= $urun['id'] ?></td>
                    <td><?= htmlspecialchars($urun['type']) ?></td>
                    <td><?= htmlspecialchars($urun['name']) ?></td>
                    <td><?= number_format($urun['price'], 2) ?> ₺</td>
                    <td><?= $urun['created_at'] ?></td>
                    <td>
                        <a href="urun_duzenle.php?id=<?= $urun['id'] ?>" class="btn btn-duzenle">✏ Düzenle</a>
                        <a href="urun_listele.php?sil=<?= $urun['id'] ?>" class="btn btn-sil" onclick="return confirm('❗ Bu ürünü silmek istediğinize emin misiniz?')">🗑 Sil</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="no-data">❌ Henüz ürün eklenmedi.</div>
    <?php endif; ?>
</div>

</body>
</html>

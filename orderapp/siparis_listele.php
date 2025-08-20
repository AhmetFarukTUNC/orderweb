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

/* ✅ Kullanıcı ID */
$user_id = $_SESSION['user_id'];

/* ✅ Silme işlemi (Sadece kullanıcının kendi siparişlerini silebilir) */
if (isset($_GET['sil'])) {
    $siparis_id = intval($_GET['sil']);
    $sil = $pdo->prepare("DELETE FROM orders WHERE id = ? AND user_id = ?");
    $sil->execute([$siparis_id, $user_id]);
    header("Location: siparis_listele.php?mesaj=deleted");
    exit();
}

/* ✅ Sadece oturum açan kullanıcının siparişlerini çek */
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id asc");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$message = "";
if (isset($_GET['mesaj']) && $_GET['mesaj'] == "deleted") {
    $message = "✅ Sipariş başarıyla silindi.";
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>📜 Sipariş Listesi</title>
<style>
    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #667eea, #764ba2);
        margin: 0;
        padding: 40px;
        color: #fff;
    }
    .container {
        background: #fff;
        color: #333;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        max-width: 1100px;
        margin: auto;
    }
    h2 {
        text-align: center;
        color: #667eea;
        margin-bottom: 25px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }
    th, td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #ddd;
        font-size: 15px;
    }
    th {
        background-color: #667eea;
        color: #fff;
        text-transform: uppercase;
        font-size: 14px;
    }
    tr:hover {
        background-color: #f1f1f1;
    }
    .btn {
        display: inline-block;
        padding: 10px 18px;
        margin-top: 20px;
        background: #667eea;
        color: #fff;
        text-decoration: none;
        border-radius: 10px;
        transition: 0.3s;
    }
    .btn:hover {
        background: #5563c1;
    }
    /* 🔥 Yeni Sil Butonu Tasarımı */
    .delete-btn {
        background: #ff4b5c;
        color: white;
        padding: 8px 14px;
        border-radius: 50px;
        font-size: 14px;
        font-weight: bold;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.3s ease;
        box-shadow: 0 3px 8px rgba(255, 75, 92, 0.3);
    }
    .delete-btn:hover {
        background: #e63946;
        transform: translateY(-2px);
        box-shadow: 0 5px 12px rgba(230, 57, 70, 0.4);
    }
    .delete-icon {
        font-size: 16px;
    }
    .message {
        background: #d4edda;
        color: #155724;
        padding: 10px;
        margin-bottom: 20px;
        border-radius: 8px;
        font-weight: bold;
        text-align: center;
    }
</style>
<script>
function confirmDelete(id) {
    if (confirm("❗ Bu siparişi silmek istediğinizden emin misiniz?")) {
        window.location.href = "siparis_listele.php?sil=" + id;
    }
}
</script>
</head>
<body>
    <div class="container">
        <h2>📜 Sipariş Listesi</h2>

        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if (count($orders) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Siparişi Verenin Adı</th>
                    <th>Yer</th>
                    <th>Ürünler</th>
                    <th>İçecekler</th>
                    <th>Toplam Tutar (₺)</th>
                    <th>Tarih</th>
                    <th>Onay Durumu</th>
                    <th>Ödeme Durumu</th> <!-- ✅ Yeni sütun -->
                    <th cols="2">İşlem</th>
                    
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= $order['id'] ?></td>
                    <td><?= htmlspecialchars($order['siparisi_alan']) ?></td>
                    <td><?= htmlspecialchars($order['yer']) ?></td>
                    <td><?= htmlspecialchars($order['urunler']) ?></td>
                    <td><?= htmlspecialchars($order['icecekler']) ?></td>
                    <td><strong><?= number_format($order['toplam_tutar'], 2, ',', '.') ?> ₺</strong></td>
                    <td><?= $order['created_at'] ?></td>
                    <td><?= htmlspecialchars($order['onay_durumu']) ?></td>
                    <td><?= htmlspecialchars($order['odeme_durumu']) ?></td> <!-- ✅ DB'den çekme -->
                    <td>
                        <a href="javascript:void(0)" onclick="confirmDelete(<?= $order['id'] ?>)" class="delete-btn">
                            <span class="delete-icon">🗑</span> Sil
                        </a>
                        <a href="siparis_duzenle.php?id=<?= $order['id'] ?>" class="btn btn-duzenle">✏ Düzenle</a>
                    </td>
                    

                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>📭 Henüz hiç sipariş bulunmuyor.</p>
        <?php endif; ?>

        <a href="homepage.php" class="btn">⬅ Anasayfaya Dön</a>
    </div>
</body>
</html>

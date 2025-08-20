<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/* Veritabanı bağlantısı */
$host = "localhost";
$dbname = "siparis_sistemi";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

/* Silme işlemi */
if (isset($_GET['sil'])) {
    $siparis_id = intval($_GET['sil']);
    $sil = $pdo->prepare("DELETE FROM orders WHERE id = ?");
    $sil->execute([$siparis_id]);
    header("Location: orders_goster.php?mesaj=deleted");
    exit();
}

/* Onay durumu güncelleme */
if (isset($_GET['onay']) && isset($_GET['id'])) {
    $siparis_id = intval($_GET['id']);
    $onay = $_GET['onay'] === 'evet' ? 'onaylandı' : 'onaylanmadı';

    $guncelle = $pdo->prepare("UPDATE orders SET onay_durumu = ? WHERE id = ?");
    $guncelle->execute([$onay, $siparis_id]);
    header("Location: orders_goster.php?mesaj=updated");
    exit();
}

/* Ödeme durumu güncelleme - YENİ EKLENDİ */
if (isset($_GET['odeme']) && isset($_GET['id'])) {
    $siparis_id = intval($_GET['id']);
    $odeme = $_GET['odeme'] === 'evet' ? 'ödendi' : 'ödenmedi';

    $guncelle = $pdo->prepare("UPDATE orders SET odeme_durumu = ? WHERE id = ?");
    $guncelle->execute([$odeme, $siparis_id]);
    header("Location: orders_goster.php?mesaj=odeme_updated");
    exit();
}

/* Siparişleri getir */
$stmt = $pdo->prepare("SELECT * FROM orders ORDER BY id asc");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Mesajlar */
$message = "";
if (isset($_GET['mesaj'])) {
    if ($_GET['mesaj'] == "deleted") {
        $message = "✅ Sipariş başarıyla silindi.";
    } elseif ($_GET['mesaj'] == "updated") {
        $message = "✅ Onay durumu güncellendi.";
    } elseif ($_GET['mesaj'] == "odeme_updated") {
        $message = "✅ Ödeme durumu güncellendi.";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
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
    .btn, .btn-small {
        display: inline-block;
        padding: 8px 14px;
        margin-top: 5px;
        background: #667eea;
        color: #fff;
        text-decoration: none;
        border-radius: 6px;
        transition: 0.3s;
        font-size: 14px;
    }
    .btn:hover, .btn-small:hover {
        background: #5563c1;
    }
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
        window.location.href = "orders_goster.php?sil=" + id;
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
                    <th>Siparişi Veren Kişinin Adı</th>
                    <th>Yer</th>
                    <th>Ürünler</th>
                    <th>İçecekler</th>
                    <th>Toplam Tutar (₺)</th>
                    <th>Tarih</th>
                    <th>Onay Durumu</th>
                    <th>Ödeme Durumu</th> <!-- Yeni -->
                    <th>İşlem</th>
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
                    <td>
                        <?= htmlspecialchars($order['onay_durumu'] ?? 'beklemede') ?><br>
                        <a href="?id=<?= $order['id'] ?>&onay=evet" class="btn-small">Evet</a>
                        <a href="?id=<?= $order['id'] ?>&onay=hayır" class="btn-small" style="background:#ff4b5c;">Hayır</a>
                    </td>
                    <td>
                        <?= htmlspecialchars($order['odeme_durumu'] ?? 'beklemede') ?><br>
                        <a href="?id=<?= $order['id'] ?>&odeme=evet" class="btn-small">Evet</a>
                        <a href="?id=<?= $order['id'] ?>&odeme=hayır" class="btn-small" style="background:#ff4b5c;">Hayır</a>
                    </td>
                    <td>
                        <a href="javascript:void(0)" onclick="confirmDelete(<?= $order['id'] ?>)" class="delete-btn">
                            <span class="delete-icon">🗑</span> Sil
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>📭 Henüz hiç sipariş bulunmuyor.</p>
        <?php endif; ?>

        <a href="logout.php" class="btn">⬅ Çıkış Yap</a>
    </div>
</body>
</html>

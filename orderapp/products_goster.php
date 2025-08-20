<?php
// Veritabanı bağlantısı
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "siparis_sistemi";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("❌ Veritabanı bağlantısı başarısız: " . $conn->connect_error);
}

// Silme işlemi
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["sil_id"])) {
    $sil_id = intval($_POST["sil_id"]);
    $sil_sorgu = "DELETE FROM products WHERE id = $sil_id";
    $conn->query($sil_sorgu);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// JOIN ile products ve users tablosunu birleştiriyoruz
$query = "SELECT products.*, users.fullname AS kullanici_adi 
          FROM products 
          JOIN users ON products.user_id = users.id 
          ORDER BY products.created_at ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Ürün Listesi</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
    }
    body {
      margin: 0;
      padding: 40px 20px;
      font-family: 'Inter', sans-serif;
      background: linear-gradient(to right, #f7f8fa, #e9eff5);
    }
    .container {
      max-width: 1100px;
      margin: auto;
      background-color: #ffffff;
      border-radius: 16px;
      box-shadow: 0 12px 40px rgba(0,0,0,0.1);
      padding: 30px;
      overflow-x: auto;
    }
    h2 {
      text-align: center;
      color: #333;
      font-size: 28px;
      margin-bottom: 25px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      min-width: 800px;
    }
    th, td {
      padding: 14px 16px;
      text-align: left;
    }
    th {
      background: linear-gradient(to right, #4facfe, #00f2fe);
      color: white;
      font-weight: 600;
    }
    tr:nth-child(even) {
      background-color: #f9f9f9;
    }
    tr:hover {
      background-color: #f0faff;
    }
    td {
      color: #333;
    }
    .badge {
      padding: 5px 12px;
      border-radius: 50px;
      font-size: 13px;
      font-weight: 600;
      display: inline-block;
    }
    .badge.yiyecek {
      background: #ffe3e3;
      color: #d90429;
    }
    .badge.icecek {
      background: #d0f0fd;
      color: #0077b6;
    }
    .sil-btn {
      background-color: #ff4d4f;
      border: none;
      color: white;
      padding: 6px 12px;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      transition: background-color 0.3s;
    }
    .sil-btn:hover {
      background-color: #d9363e;
    }
    @media (max-width: 768px) {
      body {
        padding: 20px 10px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>📦 Ürün Listesi (Products)</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Kullanıcı Adı</th>
          <th>Tür</th>
          <th>Ad</th>
          <th>Fiyat (₺)</th>
          <th>Oluşturulma</th>
          <th>İşlem</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= htmlspecialchars($row['kullanici_adi']) ?></td>
              <td>
                <span class="badge <?= strtolower($row['type']) == 'yiyecek' ? 'yiyecek' : 'icecek' ?>">
                  <?= htmlspecialchars($row['type']) ?>
                </span>
              </td>
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td><?= number_format($row['price'], 2, ',', '.') ?> ₺</td>
              <td><?= date("d.m.Y H:i", strtotime($row['created_at'])) ?></td>
              <td>
                <form method="POST" onsubmit="return confirm('Bu ürünü silmek istediğinizden emin misiniz?');">
                  <input type="hidden" name="sil_id" value="<?= $row['id'] ?>">
                  <button type="submit" class="sil-btn">🗑️ Sil</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7">Ürün bulunamadı.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>

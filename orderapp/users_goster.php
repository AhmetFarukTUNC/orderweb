<?php
session_start();

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Veritabanı bağlantısı
$host = "localhost";
$dbname = "siparis_sistemi";
$username = "root";
$password = "";

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Silme işlemi
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id"])) {
        $userId = $_POST["id"];
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        header("Location: ".$_SERVER['PHP_SELF']); // Kendini yeniden yükle
        exit();
    }

    // Kullanıcıları çek
    $stmt = $db->prepare("SELECT id, fullname, email, username, password, created_at FROM users ORDER BY id ASC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Veri hatası: " . $e->getMessage();
    die();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Kullanıcılar Tablosu</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0;
      padding: 40px 20px;
      font-family: 'Inter', sans-serif;
      background: linear-gradient(to right, #f0f2f5, #dce3ec);
    }
    .container {
      max-width: 2000px;
      margin: auto;
      background-color: #ffffff;
      border-radius: 16px;
      box-shadow: 0 16px 60px rgba(0,0,0,0.1);
      padding: 30px;
      overflow-x: auto;
    }
    h2 {
      text-align: center;
      color: #2c3e50;
      font-size: 30px;
      margin-bottom: 30px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      min-width: 900px;
    }
    th, td {
      padding: 16px 20px;
      text-align: left;
      font-size: 15px;
    }
    th {
      background: linear-gradient(to right, #667eea, #764ba2);
      color: white;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    tr:nth-child(even) {
      background-color: #f7f9fc;
    }
    tr:hover {
      background-color: #ecf0f1;
    }
    td {
      color: #34495e;
    }
    .id-col {
      font-weight: bold;
      color: #6c5ce7;
    }
    .btn {
      display: inline-block;
      margin-top: 30px;
      padding: 12px 24px;
      background-color: #6c5ce7;
      color: white;
      text-decoration: none;
      border-radius: 8px;
      font-weight: 600;
      transition: background-color 0.3s;
    }
    .btn:hover {
      background-color: #5a4acb;
    }
    .delete-btn {
      padding: 6px 12px;
      background-color: #e74c3c;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
    .delete-btn:hover {
      background-color: #c0392b;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>👥 Kullanıcılar Listesi</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Ad Soyad</th>
          <th>Email</th>
          <th>Kullanıcı Adı</th>
          <th>Şifre</th>
          <th>Oluşturulma</th>
          <th>İşlem</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $user): ?>
          <tr>
            <td class="id-col"><?= htmlspecialchars($user['id']) ?></td>
            <td><?= htmlspecialchars($user['fullname']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><?= htmlspecialchars($user['password']) ?></td>
            <td><?= htmlspecialchars($user['created_at']) ?></td>
            <td>
              <form method="POST" onsubmit="return confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?');">
                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                <button type="submit" class="delete-btn">🗑 Sil</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <a href="logout.php" class="btn">⬅ Çıkış Yap</a>
  </div>
</body>
</html>

<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "siparis_sistemi";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("❌ Veritabanı bağlantısı başarısız: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 🔐 Özel kullanıcı: "bahcelievler" ve "1234" ise direkt panel.php'ye git
    if ($username === "bahcelievler" && $password === "1234") {
        $_SESSION['user_id'] = 9999; // sahte ID
        $_SESSION['username'] = "bahcelievler";
        $_SESSION['fullname'] = "Bahçelievler Kullanıcısı";
        header("Location: panel.php");
        exit();
    }

    // 🔎 Veritabanından kontrol
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['fullname'] = $user['fullname'];
            header("Location: homepage.php");
            exit();
        } else {
            $message = "❌ Hatalı şifre!";
        }
    } else {
        $message = "❌ Kullanıcı adı bulunamadı!";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Giriş Yap</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #ff9966, #ff5e62);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .container {
      background: #fff;
      padding: 40px;
      width: 400px;
      border-radius: 15px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
      animation: fadeIn 1s ease-in-out;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #333;
    }
    label {
      display: block;
      margin: 15px 0 5px;
      font-weight: 600;
      color: #444;
    }
    input {
      width: 100%;
      padding: 12px;
      border: 1px solid #ccc;
      border-radius: 10px;
      font-size: 14px;
      transition: 0.3s;
    }
    input:focus {
      border-color: #ff5e62;
      outline: none;
      box-shadow: 0 0 8px rgba(255,94,98,0.5);
    }
    button {
      width: 100%;
      padding: 14px;
      background: #ff5e62;
      border: none;
      color: #fff;
      font-size: 16px;
      border-radius: 10px;
      margin-top: 20px;
      cursor: pointer;
      font-weight: bold;
      transition: background 0.3s;
    }
    button:hover {
      background: #e84c50;
    }
    .message {
      text-align: center;
      margin-bottom: 15px;
      font-weight: bold;
      color: red;
    }
    .login-link {
      text-align: center;
      margin-top: 15px;
      font-size: 14px;
    }
    .login-link a {
      color: #ff5e62;
      text-decoration: none;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>🔑 Giriş Yap</h2>
    <?php if (!empty($message)) { echo "<div class='message'>$message</div>"; } ?>
    <form method="POST">
      <label for="username">Kullanıcı Adı</label>
      <input type="text" id="username" name="username" placeholder="Kullanıcı adınızı giriniz" required />
      <label for="password">Şifre</label>
      <input type="password" id="password" name="password" placeholder="Şifrenizi giriniz" required />
      <button type="submit">🚀 Giriş Yap</button>
    </form>
    <div class="login-link">
      Hesabın yok mu? <a href="index.php">Kayıt Ol</a>
    </div>
  </div>
</body>
</html>

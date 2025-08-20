<?php
// ✅ --- PHP KODU (Üst Kısımda Çalışır) ---
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "siparis_sistemi";

// ✅ Veritabanına bağlan
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("❌ Veritabanı bağlantısı başarısız: " . $conn->connect_error);
}

// ✅ Mesaj değişkeni
$message = "";

// ✅ Form gönderildiyse çalışır
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // ✅ Şifreler eşleşiyor mu?
    if ($password !== $confirm_password) {
        $message = "❌ Şifreler eşleşmiyor!";
    } else {
        // ✅ Şifreyi hashle
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // ✅ E-posta zaten kayıtlı mı kontrol et
        $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $message = "❌ Bu e-posta zaten kayıtlı!";
        } else {
            // ✅ Veritabanına kaydet
            $stmt = $conn->prepare("INSERT INTO users (fullname, email, username, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $fullname, $email, $username, $hashed_password);

            if ($stmt->execute()) {
                $message = "✅ Kayıt başarılı! 🎉 Giriş yapabilirsiniz.";
            } else {
                $message = "❌ Hata oluştu: " . $stmt->error;
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kayıt Ol</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #667eea, #764ba2);
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
      border-color: #667eea;
      outline: none;
      box-shadow: 0 0 8px rgba(102,126,234,0.5);
    }

    button {
      width: 100%;
      padding: 14px;
      background: #667eea;
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
      background: #5563c1;
    }

    .message {
      text-align: center;
      margin-bottom: 15px;
      font-weight: bold;
    }

    .login-link {
      text-align: center;
      margin-top: 15px;
      font-size: 14px;
    }

    .login-link a {
      color: #667eea;
      text-decoration: none;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>🚀 Hesap Oluştur</h2>

    <!-- ✅ PHP’den mesaj göster -->
    <?php if (!empty($message)) { echo "<div class='message'>$message</div>"; } ?>

    <form method="POST">
      <label for="fullname">Ad Soyad</label>
      <input type="text" id="fullname" name="fullname" placeholder="Adınızı ve Soyadınızı giriniz" required>

      <label for="email">E-posta</label>
      <input type="email" id="email" name="email" placeholder="ornek@email.com" required>

      <label for="username">Kullanıcı Adı</label>
      <input type="text" id="username" name="username" placeholder="Kullanıcı adınızı giriniz" required>

      <label for="password">Şifre</label>
      <input type="password" id="password" name="password" placeholder="Şifrenizi giriniz" required>

      <label for="confirm_password">Şifre (Tekrar)</label>
      <input type="password" id="confirm_password" name="confirm_password" placeholder="Şifrenizi tekrar giriniz" required>

      <button type="submit">✨ Kayıt Ol</button>
    </form>
    <div class="login-link">
      Zaten hesabın var mı? <a href="login.php">Giriş Yap</a>
    </div>
  </div>
</body>
</html>

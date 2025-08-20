<?php
session_start();
// Eğer kullanıcı giriş yapmamışsa login sayfasına gönder
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Kullanıcı adı ve tam adı
$username = $_SESSION['username'] ?? "Kullanıcı";
$fullname = $_SESSION['fullname'] ?? "Sayın Kullanıcı";
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Ana Sayfa - Sipariş Sistemi</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    * {
      box-sizing: border-box;
    }
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #667eea, #764ba2);
      min-height: 100vh;
      color: #fff;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 40px 20px;
    }

    header {
      width: 100%;
      max-width: 1200px;
      margin-bottom: 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    header h1 {
      font-weight: 700;
      font-size: 28px;
      margin: 0;
      letter-spacing: 1px;
      text-shadow: 1px 1px 6px rgba(0,0,0,0.3);
    }
    header .logout-btn {
      background: #ff5e62;
      border: none;
      padding: 12px 20px;
      border-radius: 12px;
      font-weight: 600;
      color: white;
      cursor: pointer;
      transition: background 0.3s ease;
      box-shadow: 0 4px 10px rgba(255,94,98,0.6);
    }
    header .logout-btn:hover {
      background: #e84c50;
      box-shadow: 0 6px 15px rgba(232, 76, 80, 0.8);
    }

    main {
      width: 100%;
      max-width: 1200px;
      display: grid;
      grid-template-columns: repeat(auto-fit,minmax(220px,1fr));
      gap: 30px;
    }

    .card {
      background: #fff;
      color: #333;
      border-radius: 20px;
      padding: 30px 20px;
      box-shadow: 0 10px 20px rgba(0,0,0,0.15);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      user-select: none;
    }
    .card:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 30px rgba(0,0,0,0.3);
    }
    .card h2 {
      margin: 0 0 15px 0;
      font-size: 22px;
      font-weight: 700;
    }
    .card svg {
      width: 60px;
      height: 60px;
      margin-bottom: 15px;
      fill: #667eea;
      transition: fill 0.3s ease;
    }
    .card:hover svg {
      fill: #764ba2;
    }

    /* Responsive */
    @media (max-width: 480px) {
      main {
        grid-template-columns: 1fr;
      }
      header {
        flex-direction: column;
        gap: 15px;
      }
      header h1 {
        text-align: center;
      }
      header .logout-btn {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <header>
    <h1>Hoş geldin, <?=htmlspecialchars($fullname)?>!</h1>
    <form method="POST" action="logout.php" style="margin:0;">
      <button class="logout-btn" type="submit">Çıkış Yap</button>
    </form>
  </header>

  <main>
    <div class="card" onclick="location.href='urun_ekle.php'">
      <svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
        <path d="M32 6a26 26 0 1 0 26 26A26.029 26.029 0 0 0 32 6Zm0 48a22 22 0 1 1 22-22 22.025 22.025 0 0 1-22 22Z"/>
        <path d="M32 16a2 2 0 0 0-2 2v10H20a2 2 0 0 0 0 4h10v10a2 2 0 0 0 4 0V32h10a2 2 0 0 0 0-4H34V18a2 2 0 0 0-2-2Z"/>
      </svg>
      <h2>Ürün Ekle</h2>
      <p>Yeni ürün eklemek için tıkla</p>
    </div>

    <div class="card" onclick="location.href='siparis_ekle.php'">
      <svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
        <path d="M12 12h40v8H12zM12 28h40v8H12zM12 44h40v8H12z"/>
      </svg>
      <h2>Sipariş Ekle</h2>
      <p>Yeni sipariş oluştur</p>
    </div>

    <div class="card" onclick="location.href='urun_listele.php'">
      <svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
        <path d="M16 12h32v8H16zM16 28h32v8H16zM16 44h32v8H16z"/>
      </svg>
      <h2>Ürünleri Listele</h2>
      <p>Mevcut ürünleri gör</p>
    </div>

    <div class="card" onclick="location.href='siparis_listele.php'">
      <svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
        <circle cx="32" cy="32" r="30" stroke="#667eea" stroke-width="4" fill="none"/>
        <path d="M32 16v16l12 8" stroke="#667eea" stroke-width="4" stroke-linecap="round" fill="none"/>
      </svg>
      <h2>Siparişleri Listele</h2>
      <p>Tüm siparişlere eriş</p>
    </div>
  </main>
</body>
</html>

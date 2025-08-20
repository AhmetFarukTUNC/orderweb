<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Kontrol Paneli</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;700&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    body {
      font-family: 'Inter', sans-serif;
      min-height: 100vh;
      background: linear-gradient(to right, #4facfe, #00f2fe);
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 40px;
    }

    .container {
      display: flex;
      flex-direction: column;
      gap: 30px; /* ✅ Kartlar arası boşluk */
      align-items: center;
      width: 100%;
      max-width: 600px;
    }

    .card {
      background: #ffffff;
      padding: 40px;
      border-radius: 20px;
      box-shadow: 0 16px 40px rgba(0,0,0,0.15);
      text-align: center;
      width: 100%;
      animation: slideFade 0.8s ease;
    }

    @keyframes slideFade {
      from {
        transform: translateY(30px);
        opacity: 0;
      }
      to {
        transform: translateY(0);
        opacity: 1;
      }
    }

    .card h1 {
      font-size: 28px;
      margin-bottom: 20px;
      color: #333;
    }

    .card p {
      font-size: 16px;
      margin-bottom: 30px;
      color: #666;
    }

    .btn {
      display: inline-block;
      background: linear-gradient(to right, #ff416c, #ff4b2b);
      color: white;
      padding: 14px 30px;
      border: none;
      border-radius: 50px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 24px rgba(255, 75, 43, 0.4);
    }

    @media (max-width: 500px) {
      .card {
        padding: 30px 20px;
      }
      .card h1 {
        font-size: 22px;
      }
      .btn {
        font-size: 15px;
        padding: 12px 24px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="card">
      <h1>📊 Ürün Kontrol Paneli</h1>
      <p>Ürün listesini görmek için aşağıdaki butona tıklayın.</p>
      <a href="products_goster.php" class="btn">🚀 Ürünleri Göster</a>
    </div>

    <div class="card">
      <h1>🧾 Sipariş Kontrol Paneli</h1>
      <p>Sipariş listesini görmek için aşağıdaki butona tıklayın.</p>
      <a href="orders_goster.php" class="btn">📄 Siparişleri Göster</a>
    </div>

    <div class="card">
  <h1>👤 Kullanıcı Kontrol Paneli</h1>
  <p>Kullanıcı işlemleri için aşağıdaki butona tıklayın.</p>
  <a href="users_goster.php" class="btn">🛠️ Kullanıcı Paneline Git</a>
</div>

  </div>
</body>
</html>

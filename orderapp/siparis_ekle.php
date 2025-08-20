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

    // ✅ products tablosunda user_id kolonu var mı, yoksa ekle
    $checkCol = $pdo->query("SHOW COLUMNS FROM products LIKE 'user_id'");
    if ($checkCol->rowCount() === 0) {
        $pdo->exec("ALTER TABLE products ADD user_id INT AFTER id");
    }

    // ✅ orders tablosunda user_id kolonu var mı, yoksa ekle
    $checkOrderCol = $pdo->query("SHOW COLUMNS FROM orders LIKE 'user_id'");
    if ($checkOrderCol->rowCount() === 0) {
        $pdo->exec("ALTER TABLE orders ADD user_id INT AFTER id");
    }

} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

/* ✅ Kullanıcı ID (session’dan) */
$user_id = $_SESSION['user_id'];

/* ✅ Yiyecek ve içecekleri sadece bu kullanıcıya ait olanlardan çek */
$urunler_stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE type = 'Yiyecek' AND user_id = ? ORDER BY name ASC");
$urunler_stmt->execute([$user_id]);
$urunler = $urunler_stmt->fetchAll(PDO::FETCH_ASSOC);

$icecekler_stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE type = 'İçecek' AND user_id = ? ORDER BY name ASC");
$icecekler_stmt->execute([$user_id]);
$icecekler = $icecekler_stmt->fetchAll(PDO::FETCH_ASSOC);

/* ✅ Yerler */
$yerler = [];
for ($i = 1; $i <= 10; $i++) {
    $yerler[] = ['id' => $i, 'name' => "Aile $i"];
}
for ($i = 11; $i <= 20; $i++) {
    $yerler[] = ['id' => $i, 'name' => "Erkekler " . ($i - 10)];
}

for ($i = 22; $i <= 22; $i++) {
    $yerler[] = ['id' => $i, 'name' => "Kasa " . ($i - 21)];
}
for ($i = 33; $i <= 33; $i++) {
    $yerler[] = ['id' => $i, 'name' => "Çadır"];
}
/* ✅ ID → ürün objesi eşlemesi */
$urunMap = [];
foreach ($urunler as $urun) {
    $urunMap[$urun['id']] = $urun;
}
foreach ($icecekler as $icecek) {
    $urunMap[$icecek['id']] = $icecek;
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siparisi_alan = trim($_POST['siparisi_alan']);
    $yer_id = $_POST['yer'] ?? null;
    $urun_secimleri = $_POST['urunler'] ?? [];
    $icecek_secimleri = $_POST['icecekler'] ?? [];

    if (empty($siparisi_alan)) {
        $message = "❌ Siparişi alan ismi zorunludur.";
    } elseif (empty($yer_id)) {
        $message = "❌ Yer seçimi zorunludur.";
    } else {
        // ✅ Yer ismini bul
        $yer_adi = '';
        foreach ($yerler as $y) {
            if ($y['id'] == $yer_id) {
                $yer_adi = $y['name'];
                break;
            }
        }

        // ✅ ID listesinden isim listesi döner
        function getNamesByIds($ids, $map) {
            $names = [];
            foreach ($ids as $id) {
                if (isset($map[$id])) {
                    $names[] = $map[$id]['name'];
                }
            }
            return $names;
        }

        // ✅ Boşları filtrele
        $urunler_filt = array_filter($urun_secimleri);
        $icecekler_filt = array_filter($icecek_secimleri);

        $urun_isimleri = getNamesByIds($urunler_filt, $urunMap);
        $icecek_isimleri = getNamesByIds($icecekler_filt, $urunMap);

        // ✅ Toplam tutar hesapla
        $toplam_tutar = 0;
        foreach ($urunler_filt as $id) {
            $toplam_tutar += $urunMap[$id]['price'];
        }
        foreach ($icecekler_filt as $id) {
            $toplam_tutar += $urunMap[$id]['price'];
        }

        // ✅ Virgülle ayrılmış isim listesi oluştur
        $urun_str = implode(", ", $urun_isimleri);
        $icecek_str = implode(", ", $icecek_isimleri);

        // ✅ Siparişi veritabanına ekle (user_id ile birlikte)
        $insert = $pdo->prepare("INSERT INTO orders (user_id, siparisi_alan, yer, urunler, icecekler, toplam_tutar) VALUES (?,?,?,?,?,?)");
        $insert->execute([$user_id, $siparisi_alan, $yer_adi, $urun_str, $icecek_str, $toplam_tutar]);

        $message = "✅ Sipariş başarıyla kaydedildi. Toplam Tutar: ₺" . number_format($toplam_tutar, 2, ',', '.');
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Sipariş Ekle</title>
<style>
  body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg,#667eea,#764ba2);
    color: #fff;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    min-height: 100vh;
    padding: 40px 20px;
  }
  .container {
    background: #fff;
    color: #333;
    border-radius: 15px;
    padding: 30px 40px;
    width: 600px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.2);
  }
  h2 {
    text-align: center;
    margin-bottom: 25px;
    color: #667eea;
  }
  label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
  }
  input[type=text], select {
    width: 100%;
    padding: 12px 15px;
    margin-bottom: 20px;
    border-radius: 10px;
    border: 1px solid #ccc;
    font-size: 16px;
    transition: 0.3s;
  }
  .dropdown-group {
    margin-bottom: 30px;
  }
  .dropdown-group label {
    font-size: 18px;
    margin-bottom: 10px;
    color: #667eea;
  }
  .dropdown-list {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
  }
  .total {
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 30px;
    text-align: center;
    color: #667eea;
  }
  button {
    width: 100%;
    padding: 15px;
    font-size: 18px;
    font-weight: 700;
    border: none;
    border-radius: 12px;
    background: #667eea;
    color: #fff;
    cursor: pointer;
    transition: background 0.3s ease;
  }
  button:hover {
    background: #5563c1;
  }
  .message {
    margin-bottom: 25px;
    text-align: center;
    font-weight: 700;
    color: green;
  }
</style>
</head>
<body>
  <div class="container">
    <h2>📋 Sipariş Ekle</h2>

    <?php if ($message): ?>
      <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" id="siparisForm">
      <label for="siparisi_alan">Siparişi Verenin Adı</label>
      <input type="text" id="siparisi_alan" name="siparisi_alan" placeholder="Siparişi veren kişi adı" required />

      <label for="yer">Yer Seçimi</label>
      <select id="yer" name="yer" required>
        <option value="">-- Yer Seçiniz --</option>
        <?php foreach ($yerler as $yer): ?>
          <option value="<?= $yer['id'] ?>"><?= htmlspecialchars($yer['name']) ?></option>
        <?php endforeach; ?>
      </select>

      <!-- Yiyecek Dropdownları -->
      <div class="dropdown-group">
        <label>Ürünler (20 adet)</label>
        <div class="dropdown-list">
          <?php for ($i=0; $i<20; $i++): ?>
            <select name="urunler[]" class="urun-secimi">
              <option value="" data-price="0">-- Ürün Seçiniz --</option>
              <?php foreach($urunler as $urun): ?>
                <option value="<?= $urun['id'] ?>" data-price="<?= $urun['price'] ?>">
                  <?= htmlspecialchars($urun['name']) ?> (₺<?= number_format($urun['price'],2,',','.') ?>)
                </option>
              <?php endforeach; ?>
            </select>
          <?php endfor; ?>
        </div>
      </div>

      <!-- İçecek Dropdownları -->
      <div class="dropdown-group">
        <label>İçecekler (20 adet)</label>
        <div class="dropdown-list">
          <?php for ($i=0; $i<20; $i++): ?>
            <select name="icecekler[]" class="icecek-secimi">
              <option value="" data-price="0">-- İçecek Seçiniz --</option>
              <?php foreach($icecekler as $icecek): ?>
                <option value="<?= $icecek['id'] ?>" data-price="<?= $icecek['price'] ?>">
                  <?= htmlspecialchars($icecek['name']) ?> (₺<?= number_format($icecek['price'],2,',','.') ?>)
                </option>
              <?php endforeach; ?>
            </select>
          <?php endfor; ?>
        </div>
      </div>

      <!-- Toplam Tutar -->
      <div class="total">Toplam Tutar: <span id="toplamTutar">0,00 ₺</span></div>

      <button type="submit">Siparişi Onayla</button>
    </form>
  </div>

  

<script>
  function hesapla() {
    let toplam = 0;

    document.querySelectorAll('.urun-secimi').forEach(select => {
      const secilen = select.options[select.selectedIndex];
      toplam += parseFloat(secilen.dataset.price || 0);
    });

    document.querySelectorAll('.icecek-secimi').forEach(select => {
      const secilen = select.options[select.selectedIndex];
      toplam += parseFloat(secilen.dataset.price || 0);
    });

    document.getElementById('toplamTutar').textContent = toplam.toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ₺';
  }

  document.querySelectorAll('.urun-secimi, .icecek-secimi').forEach(select => {
    select.addEventListener('change', hesapla);
  });

  window.onload = hesapla;
</script>
</body>
</html>
  
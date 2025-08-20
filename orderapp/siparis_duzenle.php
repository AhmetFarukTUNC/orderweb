<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require "veritabani_baglanti.php";

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) die("Sipariş ID bulunamadı.");
$id = intval($_GET['id']);

// Mevcut siparişi al
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$siparis = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$siparis) die("Sipariş bulunamadı veya yetkiniz yok.");

// Yerler dropdown için
$yerler = [];
for($i=1;$i<=10;$i++) $yerler[] = "Aile $i";
for($i=11;$i<=20;$i++) $yerler[] = "Erkekler ".($i-10);
$yerler[] = "Kasa";
$yerler[] = "Çadır";

// Ürünler ve içecekler
$urunler_stmt = $pdo->prepare("SELECT id,name,price FROM products WHERE type='Yiyecek' AND user_id=? ORDER BY name ASC");
$urunler_stmt->execute([$user_id]);
$urunler = $urunler_stmt->fetchAll(PDO::FETCH_ASSOC);

$icecekler_stmt = $pdo->prepare("SELECT id,name,price FROM products WHERE type='İçecek' AND user_id=? ORDER BY name ASC");
$icecekler_stmt->execute([$user_id]);
$icecekler = $icecekler_stmt->fetchAll(PDO::FETCH_ASSOC);

// Ürün/İçecek map (id => ürün)
$urunMap = [];
foreach(array_merge($urunler,$icecekler) as $p) $urunMap[$p['id']] = $p;

// Siparişteki seçili ürünler
$secili_urunler = $siparis['urunler'] ? array_map('trim', explode(',', $siparis['urunler'])) : [];
$secili_icecekler = $siparis['icecekler'] ? array_map('trim', explode(',', $siparis['icecekler'])) : [];

// ID bulma fonksiyonu
function getIdByName($name, $map){
    foreach($map as $id => $u) if($u['name']==$name) return $id;
    return null;
}

// Seçili ürün/icecek IDleri
$secili_urun_idler = array_map(fn($n)=>getIdByName($n,$urunMap), $secili_urunler);
$secili_icecek_idler = array_map(fn($n)=>getIdByName($n,$urunMap), $secili_icecekler);

// Güncelleme
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $siparisi_alan = $_POST['siparisi_alan'];
    $yer = $_POST['yer'];
    $urun_secimleri = $_POST['urunler'] ?? [];
    $icecek_secimleri = $_POST['icecekler'] ?? [];

    $urun_isimleri = [];
    foreach($urun_secimleri as $idU) if(isset($urunMap[$idU])) $urun_isimleri[] = $urunMap[$idU]['name'];

    $icecek_isimleri = [];
    foreach($icecek_secimleri as $idI) if(isset($urunMap[$idI])) $icecek_isimleri[] = $urunMap[$idI]['name'];

    // Toplam tutar
    $toplam_tutar = 0;
    foreach(array_merge($urun_secimleri,$icecek_secimleri) as $idItem) if(isset($urunMap[$idItem])) $toplam_tutar += $urunMap[$idItem]['price'];

    // Güncelle
    $update = $pdo->prepare("UPDATE orders SET siparisi_alan=?, yer=?, urunler=?, icecekler=?, toplam_tutar=? WHERE id=? AND user_id=?");
    $update->execute([
        $siparisi_alan,
        $yer,
        implode(', ',$urun_isimleri),
        implode(', ',$icecek_isimleri),
        $toplam_tutar,
        $id,
        $user_id
    ]);

    header("Location: siparis_listele.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Sipariş Düzenle</title>
<style>
body{font-family:Arial,sans-serif;background:#f4f4f4;padding:20px}
.container{background:white;padding:20px;border-radius:10px;width:600px;margin:auto;box-shadow:0 4px 10px rgba(0,0,0,0.1)}
select,input,button{width:100%;padding:10px;margin-top:10px;border-radius:5px;border:1px solid #ddd;font-size:16px}
button{background:#5b86e5;color:white;border:none;cursor:pointer}
button:hover{background:#476dc5}
.dropdown-list{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-bottom:20px}
.total{font-weight:bold;margin-top:10px;text-align:center;font-size:18px;}
</style>
</head>
<body>
<div class="container">
<h2>✏ Sipariş Düzenle</h2>
<form method="post">
<label>Siparişi Alan:</label>
<input type="text" name="siparisi_alan" value="<?= htmlspecialchars($siparis['siparisi_alan']) ?>" required>

<label>Yer:</label>
<select name="yer" required>
<option value="">-- Yer Seçiniz --</option>
<?php foreach($yerler as $y): ?>
<option value="<?= $y ?>" <?= $siparis['yer']==$y?'selected':'' ?>><?= htmlspecialchars($y) ?></option>
<?php endforeach; ?>
</select>

<label>Ürünler:</label>
<div class="dropdown-list">
<?php foreach($secili_urun_idler as $idU): ?>
<select name="urunler[]">
<option value="">-- Seçiniz --</option>
<?php foreach($urunler as $u): ?>
<option value="<?= $u['id'] ?>" <?= $u['id']==$idU?'selected':'' ?>>
    <?= htmlspecialchars($u['name']) ?> (₺<?= number_format($u['price'],2,',','.') ?>)
</option>
<?php endforeach; ?>
</select>
<?php endforeach; ?>
</div>

<label>İçecekler:</label>
<div class="dropdown-list">
<?php foreach($secili_icecek_idler as $idI): ?>
<select name="icecekler[]">
<option value="">-- Seçiniz --</option>
<?php foreach($icecekler as $i): ?>
<option value="<?= $i['id'] ?>" <?= $i['id']==$idI?'selected':'' ?>>
    <?= htmlspecialchars($i['name']) ?> (₺<?= number_format($i['price'],2,',','.') ?>)
</option>
<?php endforeach; ?>
</select>
<?php endforeach; ?>
</div>

<div class="total">Toplam Tutar: <span id="toplamTutar">0,00 ₺</span></div>
<button type="submit">💾 Kaydet</button>
</form>
</div>

<script>
// Toplam tutar hesaplama
function hesapla(){
    let toplam=0;
    document.querySelectorAll('select[name="urunler[]"], select[name="icecekler[]"]').forEach(s=>{
        const secili = s.selectedOptions[0];
        if(secili && secili.textContent.match(/₺([\d,\.]+)/)){
            toplam += parseFloat(secili.textContent.match(/₺([\d,\.]+)/)[1].replace(',','.'));
        }
    });
    document.getElementById('toplamTutar').textContent = toplam.toLocaleString('tr-TR',{minimumFractionDigits:2})+' ₺';
}
document.querySelectorAll('select[name="urunler[]"], select[name="icecekler[]"]').forEach(s=>s.addEventListener('change',hesapla));
window.onload=hesapla;
</script>
</body>
</html>

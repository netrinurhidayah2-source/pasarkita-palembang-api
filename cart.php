<?php
include 'config.php';

// --- AKSI ---
// Hapus 1 item
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM keranjang_detail WHERE id = $id_hapus");
    header("Location: cart.php");
    exit;
}
// Kosongkan semua
if (isset($_GET['kosongkan'])) {
    mysqli_query($conn, "DELETE FROM keranjang_detail");
    header("Location: cart.php");
    exit;
}
// Update jumlah
if (isset($_POST['update_jumlah'])) {
    foreach ($_POST['jumlah'] as $id_detail => $jml) {
        $id_detail = (int)$id_detail;
        $jml = (int)$jml;
        if ($jml < 1) $jml = 1;
        mysqli_query($conn, "UPDATE keranjang_detail SET jumlah = $jml WHERE id = $id_detail");
    }
    header("Location: cart.php");
    exit;
}

// --- AMBIL ISI KERANJANG ---
// Ambil semua yang statusnya masih Aktif, urut terbaru
$q = mysqli_query($conn, "
    SELECT kd.*, p.nama_produk, p.foto, p.stok, p.harga as harga_produk
    FROM keranjang_detail kd
    JOIN produk p ON kd.produk_id = p.id
    JOIN keranjang k ON kd.keranjang_id = k.id
    WHERE k.status = 'Aktif'
    ORDER BY kd.id DESC
");

$items = [];
$total = 0;
$count = 0;
while ($row = mysqli_fetch_assoc($q)) {
    $subtotal = $row['harga'] * $row['jumlah'];
    $row['subtotal'] = $subtotal;
    $total += $subtotal;
    $count += $row['jumlah'];
    $items[] = $row;
}

$ongkir = ($total >= 100000 || $total == 0) ? 0 : 10000;
$grand_total = $total + $ongkir;
$sisa_gratis_ongkir = 100000 - $total;
$progress = $total >= 100000 ? 100 : ($total / 100000 * 100);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Keranjang - PasarKita Palembang</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body { background: #FFF8E7; font-family: 'Segoe UI', sans-serif; }
  .header { background: #6B0F0F; color: white; }
  .btn-maroon { background: #6B0F0F; color: white; }
  .btn-maroon:hover { background: #4A0A0A; color: white; }
  .btn-gold { background: #C9A227; color: white; }
  .btn-gold:hover { background: #A8861B; color: white; }
  .card-produk { border: none; border-radius: 16px; }
  .foto-produk { width: 90px; height: 90px; object-fit: cover; border-radius: 12px; }
  .qty-btn { width: 32px; height: 32px; padding: 0; font-weight: bold; }
  .qty-input { width: 50px; text-align: center; font-weight: bold; }
  .summary-card { border: none; border-radius: 16px; position: sticky; top: 90px; }
  .progress { height: 8px; }
</style>
</head>
<body>

<!-- HEADER -->
<div class="header py-3 sticky-top">
  <div class="container d-flex justify-content-between align-items-center">
    <h3 class="m-0 fw-bold">PASARKITA PALEMBANG</h3>
    <a href="index.php" class="btn btn-light btn-sm fw-bold">← Lanjut Belanja</a>
  </div>
</div>

<div class="container my-4">
  <a href="index.php" class="btn btn-secondary btn-sm mb-3">← Kembali ke Beranda</a>
  <h2 class="fw-bold">🛒 Keranjang Belanja</h2>
  <p class="text-muted"><?= $count ?> item di keranjang kamu</p>

  <?php if (empty($items)): ?>
    <!-- KERANJANG KOSONG -->
    <div class="bg-white rounded shadow-sm text-center p-5">
      <div style="font-size:80px;">🛒</div>
      <h4 class="fw-bold mt-2">Keranjang Kamu Masih Kosong</h4>
      <p class="text-muted">Yuk jajan dulu, Pempek Kapal Selam lagi diskon -20% loh!</p>
      <a href="index.php" class="btn btn-maroon btn-lg px-4">Cari Pempek Sekarang →</a>
    </div>

  <?php else: ?>
    <!-- PROGRESS GRATIS ONGKIR -->
    <div class="bg-white rounded shadow-sm p-3 mb-3">
      <?php if ($total >= 100000): ?>
        <p class="mb-1 fw-bold text-success">🎉 Selamat! Kamu dapat GRATIS ONGKIR Palembang Kota!</p>
      <?php else: ?>
        <p class="mb-1">Belanja <b>Rp <?= number_format($sisa_gratis_ongkir, 0, ',', '.') ?></b> lagi untuk <b>GRATIS ONGKIR</b></p>
      <?php endif; ?>
      <div class="progress">
        <div class="progress-bar bg-success" style="width: <?= $progress ?>%"></div>
      </div>
    </div>

    <form method="POST">
    <div class="row">
      <!-- KIRI: LIST PRODUK -->
      <div class="col-md-8">
        <?php foreach ($items as $it):
          $file = 'uploads/' . $it['foto'];
          $src  = 'uploads/' . rawurlencode($it['foto']);
        ?>
        <div class="card card-produk shadow-sm mb-3">
          <div class="card-body d-flex gap-3 align-items-center">
            <a href="detail.php?id=<?= $it['produk_id'] ?>">
              <?php if (!empty($it['foto']) && file_exists($file)): ?>
                <img src="<?= $src ?>" class="foto-produk">
              <?php else: ?>
                <div class="foto-produk bg-light d-flex align-items-center justify-content-center">🍲</div>
              <?php endif; ?>
            </a>
            <div class="flex-grow-1">
              <span class="badge bg-warning text-dark">Asli Palembang</span>
              <h5 class="fw-bold mb-0 mt-1"><?= htmlspecialchars($it['nama_produk']) ?></h5>
              <p class="text-muted mb-1">Stok: <?= $it['stok'] ?> | Rp <?= number_format($it['harga'], 0, ',', '.') ?> / pcs</p>
              <p class="fw-bold mb-0" style="color:#6B0F0F;">Subtotal: Rp <?= number_format($it['subtotal'], 0, ',', '.') ?></p>
            </div>
            <div class="text-end">
              <div class="d-flex align-items-center justify-content-end gap-1 mb-2">
                <button type="button" class="btn btn-outline-secondary qty-btn" onclick="ubahQty(<?= $it['id'] ?>, -1)">-</button>
                <input type="number" name="jumlah[<?= $it['id'] ?>]" id="qty-<?= $it['id'] ?>" value="<?= $it['jumlah'] ?>" min="1" max="<?= $it['stok'] ?>" class="form-control qty-input" readonly>
                <button type="button" class="btn btn-outline-secondary qty-btn" onclick="ubahQty(<?= $it['id'] ?>, 1, <?= $it['stok'] ?>)">+</button>
              </div>
              <a href="cart.php?hapus=<?= $it['id'] ?>" onclick="return confirm('Hapus <?= htmlspecialchars($it['nama_produk']) ?>?')" class="btn btn-outline-danger btn-sm">🗑️ Hapus</a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>

        <div class="d-flex gap-2">
          <button type="submit" name="update_jumlah" class="btn btn-gold fw-bold">🔄 Update Jumlah</button>
          <a href="cart.php?kosongkan=1" onclick="return confirm('Kosongkan semua keranjang?')" class="btn btn-outline-secondary">Kosongkan Keranjang</a>
        </div>
      </div>

      <!-- KANAN: RINGKASAN -->
      <div class="col-md-4">
        <div class="card summary-card shadow-sm">
          <div class="card-body p-4">
            <h5 class="fw-bold">Ringkasan Belanja</h5>
            <hr>
            <div class="d-flex justify-content-between mb-2">
              <span>Total Harga (<?= $count ?> item)</span>
              <span class="fw-bold">Rp <?= number_format($total, 0, ',', '.') ?></span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span>Ongkir Palembang Kota</span>
              <?php if ($ongkir == 0): ?>
                <span class="fw-bold text-success">GRATIS</span>
              <?php else: ?>
                <span class="fw-bold">Rp <?= number_format($ongkir, 0, ',', '.') ?></span>
              <?php endif; ?>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span>Diskon</span>
              <span class="fw-bold text-success">- Rp 0</span>
            </div>
            <hr>
            <div class="d-flex justify-content-between mb-1">
              <span class="fw-bold fs-5">Total Bayar</span>
              <span class="fw-bold fs-5" style="color:#6B0F0F;">Rp <?= number_format($grand_total, 0, ',', '.') ?></span>
            </div>
            <p class="text-muted small">Estimasi sampai: 1-2 jam (Palembang Kota)</p>

            <a href="checkout.php" class="btn btn-maroon w-100 btn-lg fw-bold">Checkout →</a>
            <a href="index.php" class="btn btn-outline-secondary w-100 mt-2">Lanjut Belanja</a>

            <div class="mt-3 p-2 rounded bg-light small">
              ✅ <b>Pembayaran:</b> COD / Transfer / QRIS<br>
              ✅ <b>Garansi:</b> Pempek tidak enak, uang kembali<br>
              ✅ <b>Penjual:</b> UMKM Palembang Terverifikasi
            </div>
          </div>
        </div>
      </div>
    </div>
    </form>
  <?php endif; ?>
</div>

<script>
function ubahQty(id, n, stok = 99) {
  let input = document.getElementById('qty-' + id);
  let val = parseInt(input.value) + n;
  if (val < 1) val = 1;
  if (val > stok) val = stok;
  input.value = val;
}
</script>
</body>
</html>
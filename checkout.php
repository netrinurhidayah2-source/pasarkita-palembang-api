<?php
include 'config.php';

// --- AMBIL ISI KERANJANG ---
$q = mysqli_query($conn, "
    SELECT kd.*, p.nama_produk, p.foto, p.stok
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
    $row['subtotal'] = $row['harga'] * $row['jumlah'];
    $total += $row['subtotal'];
    $count += $row['jumlah'];
    $items[] = $row;
}

$ongkir = ($total >= 100000 || $total == 0) ? 0 : 10000;
$grand_total = $total + $ongkir;

// --- PROSES BUAT PESANAN ---
$sukses = false;
$pesanan_id_baru = 0;
if (isset($_POST['buat_pesanan']) && !empty($items)) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $wa = mysqli_real_escape_string($conn, $_POST['wa']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $metode = mysqli_real_escape_string($conn, $_POST['metode']);
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan'] ?? '');

    // 1. Buat pesanan
    mysqli_query($conn, "INSERT INTO pesanan (pengguna_id, total, tanggal, status) VALUES (1, $grand_total, NOW(), 'Menunggu Pembayaran')");
    $pesanan_id_baru = mysqli_insert_id($conn);

    // 2. Pindahkan detail keranjang -> pesanan_detail + kurangi stok
    foreach ($items as $it) {
        $pid = $it['produk_id'];
        $jml = $it['jumlah'];
        $hrg = $it['harga'];
        mysqli_query($conn, "INSERT INTO pesanan_detail (pesanan_id, produk_id, jumlah, harga) VALUES ($pesanan_id_baru, $pid, $jml, $hrg)");
        mysqli_query($conn, "UPDATE produk SET stok = stok - $jml WHERE id = $pid");
    }

    // 3. Kosongkan keranjang
    mysqli_query($conn, "DELETE FROM keranjang_detail");
    mysqli_query($conn, "UPDATE keranjang SET status='Selesai' WHERE status='Aktif'");

    $sukses = true;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout - PasarKita Palembang</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body { background: #FFF8E7; font-family: 'Segoe UI', sans-serif; }
  .header { background: #6B0F0F; color: white; }
  .btn-maroon { background: #6B0F0F; color: white; }
  .btn-maroon:hover { background: #4A0A0A; color: white; }
  .card-box { border: none; border-radius: 16px; }
  .foto-produk { width: 60px; height: 60px; object-fit: cover; border-radius: 10px; }
  .step { width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; }
  .step-aktif { background: #6B0F0F; color: white; }
  .step-done { background: #198754; color: white; }
  .step-off { background: #E9ECEF; color: #888; }
  .metode-card { cursor: pointer; border: 2px solid #E9ECEF; border-radius: 12px; padding: 12px; }
  .metode-card:hover { border-color: #C9A227; }
  input[type="radio"]:checked + .metode-card { border-color: #6B0F0F; background: #FFF8E7; }
  input[type="radio"] { display: none; }
</style>
</head>
<body>

<div class="header py-3 sticky-top">
  <div class="container d-flex justify-content-between align-items-center">
    <h3 class="m-0 fw-bold">PASARKITA PALEMBANG</h3>
    <a href="cart.php" class="btn btn-light btn-sm fw-bold">← Kembali ke Keranjang</a>
  </div>
</div>

<div class="container my-4">
  <a href="cart.php" class="btn btn-secondary btn-sm mb-3">← Kembali ke Keranjang</a>

  <!-- STEPS -->
  <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
    <span class="step step-done">✓</span> <b>Keranjang</b>
    <span>———</span>
    <span class="step step-aktif">2</span> <b>Checkout</b>
    <span>———</span>
    <span class="step step-off">3</span> <span class="text-muted">Pesanan Selesai</span>
  </div>

  <?php if ($sukses): ?>
    <!-- SUKSES -->
    <div class="bg-white rounded shadow-sm text-center p-5">
      <div style="font-size:80px;">🎉</div>
      <h3 class="fw-bold mt-2">Pesanan Berhasil Dibuat!</h3>
      <p class="text-muted">Nomor Pesanan: <b style="color:#6B0F0F;">#PK-<?= str_pad($pesanan_id_baru, 4, '0', STR_PAD_LEFT) ?></b></p>
      <p>Total yang harus dibayar: <b class="fs-4" style="color:#6B0F0F;">Rp <?= number_format($grand_total, 0, ',', '.') ?></b></p>
      <div class="alert alert-warning text-start mx-auto" style="max-width:500px;">
        <b>💳 Pembayaran Transfer:</b><br>
        BCA 123-456-7890 a.n PasarKita Palembang<br>
        Kirim bukti ke WA 0812-3456-7890
      </div>
      <a href="index.php" class="btn btn-maroon btn-lg px-4">Belanja Lagi →</a>
      <a href="cart.php" class="btn btn-outline-secondary btn-lg px-4">Lihat Keranjang</a>
    </div>

  <?php elseif (empty($items)): ?>
    <div class="bg-white rounded shadow-sm text-center p-5">
      <div style="font-size:80px;">🛒</div>
      <h4 class="fw-bold mt-2">Keranjang Kamu Kosong</h4>
      <p class="text-muted">Belum ada yang bisa di-checkout. Yuk belanja dulu!</p>
      <a href="index.php" class="btn btn-maroon btn-lg px-4">Cari Pempek Sekarang →</a>
    </div>

  <?php else: ?>
  <h2 class="fw-bold">Checkout</h2>
  <p class="text-muted"><?= $count ?> item • Estimasi sampai 1-2 jam (Palembang Kota)</p>

  <form method="POST">
  <div class="row">
    <!-- KIRI: FORM -->
    <div class="col-md-7">
      <!-- 1. DATA PENERIMA -->
      <div class="card card-box shadow-sm mb-3">
        <div class="card-body p-4">
          <h5 class="fw-bold">📍 1. Data Penerima</h5>
          <hr>
          <label class="fw-bold">Nama Pembeli *</label>
          <input type="text" name="nama" class="form-control mb-3" placeholder="Contoh: Budi Santoso" required>

          <label class="fw-bold">No. WhatsApp *</label>
          <input type="text" name="wa" class="form-control mb-3" placeholder="Contoh: 081234567890" required>

          <label class="fw-bold">Alamat Pengiriman *</label>
          <textarea name="alamat" class="form-control mb-3" rows="3" placeholder="Contoh: Jl. Merdeka No. 10, Kec. Ilir Barat I, Palembang" required></textarea>

          <label class="fw-bold">Catatan untuk Penjual (Opsional)</label>
          <input type="text" name="catatan" class="form-control" placeholder="Contoh: Cuko dipisah ya, jangan pedas">
        </div>
      </div>

      <!-- 2. METODE PEMBAYARAN -->
      <div class="card card-box shadow-sm mb-3">
        <div class="card-body p-4">
          <h5 class="fw-bold">💳 2. Metode Pembayaran</h5>
          <hr>
          <div class="row g-2">
            <div class="col-6">
              <label>
                <input type="radio" name="metode" value="COD" checked>
                <div class="metode-card text-center">
                  <div style="font-size:28px;">💵</div>
                  <b>COD</b><br><small class="text-muted">Bayar di tempat</small>
                </div>
              </label>
            </div>
            <div class="col-6">
              <label>
                <input type="radio" name="metode" value="Transfer">
                <div class="metode-card text-center">
                  <div style="font-size:28px;">🏦</div>
                  <b>Transfer Bank</b><br><small class="text-muted">BCA / BRI / Mandiri</small>
                </div>
              </label>
            </div>
            <div class="col-6">
              <label>
                <input type="radio" name="metode" value="QRIS">
                <div class="metode-card text-center">
                  <div style="font-size:28px;">📱</div>
                  <b>QRIS</b><br><small class="text-muted">Scan QR</small>
                </div>
              </label>
            </div>
            <div class="col-6">
              <label>
                <input type="radio" name="metode" value="DANA">
                <div class="metode-card text-center">
                  <div style="font-size:28px;">👛</div>
                  <b>DANA / OVO</b><br><small class="text-muted">E-Wallet</small>
                </div>
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- KANAN: RINGKASAN -->
    <div class="col-md-5">
      <div class="card card-box shadow-sm" style="position: sticky; top: 90px;">
        <div class="card-body p-4">
          <h5 class="fw-bold">🧾 Ringkasan Pesanan</h5>
          <hr>
          <?php foreach ($items as $it):
            $file = 'uploads/' . $it['foto'];
            $src  = 'uploads/' . rawurlencode($it['foto']);
          ?>
          <div class="d-flex gap-2 align-items-center mb-3">
            <?php if (!empty($it['foto']) && file_exists($file)): ?>
              <img src="<?= $src ?>" class="foto-produk">
            <?php else: ?>
              <div class="foto-produk bg-light d-flex align-items-center justify-content-center">🍲</div>
            <?php endif; ?>
            <div class="flex-grow-1">
              <b><?= htmlspecialchars($it['nama_produk']) ?></b><br>
              <small class="text-muted"><?= $it['jumlah'] ?> x Rp <?= number_format($it['harga'], 0, ',', '.') ?></small>
            </div>
            <b>Rp <?= number_format($it['subtotal'], 0, ',', '.') ?></b>
          </div>
          <?php endforeach; ?>

          <hr>
          <div class="d-flex justify-content-between mb-2">
            <span>Subtotal (<?= $count ?> item)</span>
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
          <?php if ($ongkir == 0): ?>
            <div class="alert alert-success py-2 small">🎉 Kamu dapat GRATIS ONGKIR karena belanja ≥ Rp 100.000</div>
          <?php endif; ?>
          <div class="d-flex justify-content-between mb-3">
            <span class="fw-bold fs-5">Total Bayar</span>
            <span class="fw-bold fs-5" style="color:#6B0F0F;">Rp <?= number_format($grand_total, 0, ',', '.') ?></span>
          </div>

          <button type="submit" name="buat_pesanan" class="btn btn-maroon w-100 btn-lg fw-bold">Buat Pesanan →</button>
          <p class="text-center text-muted small mt-2">Dengan klik Buat Pesanan, kamu menyetujui syarat & ketentuan PasarKita</p>

          <div class="mt-2 p-2 rounded bg-light small">
            ✅ <b>Garansi:</b> Pempek tidak enak, uang kembali<br>
            ✅ <b>Pengiriman:</b> Kurir lokal Palembang, 1-2 jam<br>
            ✅ <b>Penjual:</b> UMKM Palembang Terverifikasi
          </div>
        </div>
      </div>
    </div>
  </div>
  </form>
  <?php endif; ?>
</div>
</body>
</html>
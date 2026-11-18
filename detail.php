<?php
include 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id == 0) {
    echo "Produk tidak ditemukan. <a href='index.php'>Kembali</a>";
    exit;
}

$query = mysqli_query($conn, "
    SELECT produk.*, kategori.nama_kategori
    FROM produk
    LEFT JOIN kategori ON produk.kategori_id = kategori.id
    WHERE produk.id = $id
");
$produk = mysqli_fetch_assoc($query);
if (!$produk) {
    echo "Produk tidak ditemukan. <a href='index.php'>Kembali</a>";
    exit;
}

$pesan = '';
if (isset($_POST['tambah_keranjang'])) {
    $jumlah = (int)$_POST['jumlah'];
    if ($jumlah < 1) $jumlah = 1;
    if ($jumlah > $produk['stok']) $jumlah = $produk['stok'];
    $harga = $produk['harga'];

    mysqli_query($conn, "INSERT INTO keranjang (pengguna_id, tanggal, status) VALUES (1, NOW(), 'Aktif')");
    $keranjang_id = mysqli_insert_id($conn);
    mysqli_query($conn, "INSERT INTO keranjang_detail (keranjang_id, produk_id, jumlah, harga) VALUES ($keranjang_id, $id, $jumlah, $harga)");

    $pesan = "Berhasil! $jumlah x " . $produk['nama_produk'] . " masuk keranjang. Total Rp " . number_format($harga * $jumlah, 0, ',', '.');
}

// Foto - pakai file uploads/ kamu yang asli
$file_detail = 'uploads/' . $produk['foto'];
$src_detail  = 'uploads/' . rawurlencode($produk['foto']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($produk['nama_produk']) ?> - PasarKita Palembang</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body { background: #FFF8E7; font-family: 'Segoe UI', sans-serif; }
  .header { background: #6B0F0F; color: white; }
  .btn-maroon { background: #6B0F0F; color: white; }
  .btn-maroon:hover { background: #4A0A0A; color: white; }
  .harga-coret { text-decoration: line-through; color: #999; }
</style>
</head>
<body>

<div class="header py-3">
  <div class="container d-flex justify-content-between align-items-center">
    <h4 class="m-0 fw-bold">PASARKITA PALEMBANG</h4>
    <a href="index.php" class="btn btn-light btn-sm">← Kembali ke Beranda</a>
  </div>
</div>

<div class="container my-4">
  <?php if ($pesan): ?>
    <div class="alert alert-success"><?= $pesan ?> | <a href="cart.php">Lihat Keranjang</a></div>
  <?php endif; ?>

  <div class="row bg-white rounded shadow-sm p-4">
    <div class="col-md-6">
      <?php if (!empty($produk['foto']) && file_exists($file_detail)): ?>
        <img src="<?= $src_detail ?>" class="img-fluid rounded w-100" style="max-height:450px; object-fit:cover;">
      <?php else: ?>
        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height:400px;">Foto tidak ada: <?= htmlspecialchars($produk['foto']) ?></div>
      <?php endif; ?>
      <div class="mt-2">
        <span class="badge bg-success">100% Asli Palembang</span>
        <span class="badge bg-warning text-dark">Kategori: <?= htmlspecialchars($produk['nama_kategori'] ?? '-') ?></span>
      </div>
    </div>

    <div class="col-md-6">
      <span class="badge bg-success">Stok Tersedia: <?= $produk['stok'] ?></span>
      <span class="badge bg-warning text-dark">Terlaris di Palembang</span>

      <h2 class="fw-bold mt-2"><?= htmlspecialchars($produk['nama_produk']) ?></h2>
      <p class="text-muted">⭐ 4.9 (128 ulasan) | Terjual 200+ | Lokasi: Palembang</p>

      <h3 class="fw-bold" style="color:#6B0F0F;">Rp <?= number_format($produk['harga'], 0, ',', '.') ?></h3>
      <p>
        <span class="harga-coret">Rp <?= number_format($produk['harga'] * 1.25, 0, ',', '.') ?></span>
        <span class="badge bg-danger">-20%</span>
        <span class="badge bg-success">Gratis Ongkir Palembang Kota</span>
      </p>

      <hr>
      <h6 class="fw-bold">Deskripsi:</h6>
      <p><?= nl2br(htmlspecialchars($produk['deskripsi'])) ?></p>

      <hr>
      <form method="POST">
        <label class="fw-bold">Jumlah:</label>
        <div class="input-group mb-3" style="max-width:180px;">
          <button type="button" class="btn btn-outline-secondary" onclick="ubahJumlah(-1)">-</button>
          <input type="number" name="jumlah" id="jumlah" value="1" min="1" max="<?= $produk['stok'] ?>" class="form-control text-center" readonly>
          <button type="button" class="btn btn-outline-secondary" onclick="ubahJumlah(1)">+</button>
        </div>

        <h5>Total Harga: <span id="totalHarga" class="fw-bold" style="color:#6B0F0F;">Rp <?= number_format($produk['harga'], 0, ',', '.') ?></span></h5>

        <button type="submit" name="tambah_keranjang" class="btn btn-maroon btn-lg w-100 mt-2">Tambah ke Keranjang</button>
        <a href="index.php" class="btn btn-outline-secondary w-100 mt-2">Kembali</a>
      </form>
    </div>
  </div>
</div>

<script>
  const harga = <?= $produk['harga'] ?>;
  const stok = <?= $produk['stok'] ?>;
  function ubahJumlah(n) {
    let input = document.getElementById('jumlah');
    let val = parseInt(input.value) + n;
    if (val < 1) val = 1;
    if (val > stok) val = stok;
    input.value = val;
    document.getElementById('totalHarga').innerText = 'Rp ' + (harga * val).toLocaleString('id-ID');
  }
</script>
</body>
</html>
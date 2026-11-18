<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PasarKita Palembang</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body { background: #FFF8E7; font-family: 'Segoe UI', sans-serif; }
  .header { background: #6B0F0F; color: white; }
  .card img { height: 220px; object-fit: cover; width: 100%; }
  .card { border: none; border-radius: 12px; overflow: hidden; }
  .btn-maroon { background: #6B0F0F; color: white; }
  .btn-maroon:hover { background: #4A0A0A; color: white; }
</style>
</head>
<body>

<div class="header py-3 sticky-top">
  <div class="container d-flex justify-content-between align-items-center">
    <h3 class="m-0 fw-bold">PASARKITA PALEMBANG</h3>
    <div>
      <a href="index.php" class="btn btn-light btn-sm">Beranda</a>
      <a href="admin/produk.php" class="btn btn-warning btn-sm">Kelola Toko</a>
    </div>
  </div>
</div>

<div class="container mt-4">
  <div class="p-4 rounded text-white" style="background:#6B0F0F;">
    <h2 class="fw-bold">Oleh-Oleh Khas Palembang</h2>
    <p class="mb-0">Pempek, Tekwan, Lempok Durian, Songket & Tanjak - Langsung dari UMKM Palembang</p>
  </div>

  <div class="row mt-4">
  <?php
  $q = mysqli_query($conn, "SELECT produk.*, kategori.nama_kategori FROM produk LEFT JOIN kategori ON produk.kategori_id = kategori.id ORDER BY produk.id ASC");
  while ($p = mysqli_fetch_assoc($q)) {
    $file_foto = 'uploads/' . $p['foto'];
    $src_foto  = 'uploads/' . rawurlencode($p['foto']);
  ?>
    <div class="col-md-4 col-6 mb-4">
      <div class="card h-100 shadow-sm">
        <a href="detail.php?id=<?= $p['id'] ?>">
          <?php if (!empty($p['foto']) && file_exists($file_foto)): ?>
            <img src="<?= $src_foto ?>">
          <?php else: ?>
            <div class="bg-light d-flex align-items-center justify-content-center text-center p-3" style="height:220px;">Foto tidak ada:<br><?= htmlspecialchars($p['foto']) ?></div>
          <?php endif; ?>
        </a>
        <div class="card-body">
          <span class="badge bg-warning text-dark"><?= htmlspecialchars($p['nama_kategori'] ?? 'UMKM') ?></span>
          <h5 class="mt-2 fw-bold"><?= htmlspecialchars($p['nama_produk']) ?></h5>
          <p class="mb-1">⭐ 4.9 | Terjual 200+</p>
          <h5 class="fw-bold" style="color:#6B0F0F;">Rp <?= number_format($p['harga'], 0, ',', '.') ?></h5>
          <p class="text-muted mb-2">Stok: <?= $p['stok'] ?></p>
          <a href="detail.php?id=<?= $p['id'] ?>" class="btn btn-maroon w-100">Lihat Detail</a>
        </div>
      </div>
    </div>
  <?php } ?>
  </div>
</div>
</body>
</html>
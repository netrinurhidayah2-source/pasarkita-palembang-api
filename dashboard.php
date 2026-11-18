<?php
include '../config.php';

// --- STATISTIK ---
$total_produk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as j FROM produk"))['j'];
$total_pesanan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as j, COALESCE(SUM(total),0) as omset FROM pesanan"));
$total_kategori = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as j FROM kategori"))['j'];
$stok_habis = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as j FROM produk WHERE stok <= 0"))['j'];
$menunggu = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as j FROM pesanan WHERE status='Menunggu Pembayaran'"))['j'];

// Pesanan Terbaru
$pesanan_terbaru = mysqli_query($conn, "
    SELECT p.*, (SELECT COUNT(*) FROM pesanan_detail pd WHERE pd.pesanan_id=p.id) as jml_item
    FROM pesanan p ORDER BY p.id DESC LIMIT 5
");

// Produk Stok Menipis / Habis
$produk_stok = mysqli_query($conn, "SELECT p.*, k.nama_kategori FROM produk p LEFT JOIN kategori k ON p.kategori_id=k.id WHERE p.stok <= 5 ORDER BY p.stok ASC LIMIT 5");

// Produk Terbaru
$produk_baru = mysqli_query($conn, "SELECT p.*, k.nama_kategori FROM produk p LEFT JOIN kategori k ON p.kategori_id=k.id ORDER BY p.id DESC LIMIT 6");
?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - PasarKita Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body{ background:#FFF8E7; font-family:'Segoe UI',sans-serif; }
  .navbar-modern{ background:linear-gradient(90deg,#6B0F0F,#8B1E1E,#A52A2A); border-bottom:4px solid #C9A227; padding:16px 0; }
  .navbar-modern .navbar-brand{ color:#fff !important; font-weight:800; font-size:22px; }
  .navbar-modern .navbar-brand span{ color:#F2D57E; }
  .btn-bulat{ border-radius:50px; font-weight:700; padding:9px 20px; text-decoration:none; display:inline-block; border:none; }
  .btn-merah{ background:#8B1E1E; color:white; } .btn-merah:hover{ background:#6B0F0F; color:white; }
  .btn-emas{ background:#C9A227; color:white; } .btn-emas:hover{ background:#B8860B; color:white; }
  .btn-kembali{ background:white; color:#6B0F0F; border:1.5px solid #C9A227; } .btn-kembali:hover{ background:#FFF3CD; color:#6B0F0F; }
  .btn-tambah{ background:#0F9D58; color:white; } .btn-tambah:hover{ background:#0B7A44; color:white; }
  .card-modern{ border:none; border-radius:20px; background:white; box-shadow:0 10px 30px rgba(0,0,0,.08); overflow:hidden; }
  .hero-admin{ background:linear-gradient(135deg,#6B0F0F,#8B1E1E,#B8860B); color:white; border-radius:20px; padding:24px 28px; border:2px solid #C9A227; }
  .hero-admin h2{ font-weight:900; margin:0; }
  .judul{ font-weight:800; color:#6B0F0F; border-left:6px solid #C9A227; padding-left:12px; }
  .stat-icon{ font-size:32px; }
  .foto-thumb{ width:50px; height:50px; object-fit:cover; border-radius:10px; }
  .badge-kat{ background:#FFF3CD; color:#8B1E1E; padding:4px 10px; border-radius:20px; font-weight:700; font-size:11px; border:1px solid #C9A227; }
  table thead th{ background:#6B0F0F !important; color:#F2D57E !important; border:none; }
</style>
</head>
<body>
<nav class="navbar navbar-dark navbar-modern"><div class="container">
<a class="navbar-brand" href="../index.php">🥬 Pasar<span>Kita</span> <small style="font-size:12px;color:#F2D57E;">Admin</small></a>
<div class="d-flex gap-2">
  <a href="pesanan.php" class="btn btn-warning btn-bulat btn-sm fw-bold">🧾 Riwayat Pesanan</a>
  <a href="../index.php" class="btn btn-light btn-bulat">🏠 Lihat Toko</a>
</div>
</div></nav>

<div class="container mt-4 mb-5">
  <div class="hero-admin">
    <h2>👋 Selamat Datang, Admin PasarKita!</h2>
    <p class="mb-0">Hari ini ada <b><?= $menunggu ?> pesanan menunggu</b> • Pempek • Tekwan • Kemplang • Songket • Kain Jumputan</p>
  </div>

  <!-- MENU CEPAT -->
  <div class="d-flex gap-2 flex-wrap mt-4">
    <a href="produk.php" class="btn btn-merah btn-bulat">📦 Kelola Produk</a>
    <a href="tambah_produk.php" class="btn btn-tambah btn-bulat">➕ Tambah Produk</a>
    <a href="pesanan.php" class="btn btn-emas btn-bulat">🧾 Riwayat Pesanan</a>
    <a href="../index.php" class="btn btn-kembali btn-bulat">🏠 Lihat Toko</a>
  </div>

  <!-- STATISTIK -->
  <div class="row mt-4">
    <div class="col-6 col-md-3 mb-3"><div class="card card-modern p-3"><div class="stat-icon">📦</div><small class="text-muted">Total Produk</small><h3 class="fw-bold mb-0" style="color:#6B0F0F;"><?= $total_produk ?></h3><small><?= $stok_habis ?> produk stok habis</small></div></div>
    <div class="col-6 col-md-3 mb-3"><div class="card card-modern p-3"><div class="stat-icon">🧾</div><small class="text-muted">Total Pesanan</small><h3 class="fw-bold mb-0" style="color:#6B0F0F;"><?= $total_pesanan['j'] ?></h3><small><?= $menunggu ?> menunggu pembayaran</small></div></div>
    <div class="col-6 col-md-3 mb-3"><div class="card card-modern p-3"><div class="stat-icon">💰</div><small class="text-muted">Total Omset</small><h3 class="fw-bold mb-0" style="color:#6B0F0F;"><?= rupiah($total_pesanan['omset']) ?></h3><small>Dari semua pesanan</small></div></div>
    <div class="col-6 col-md-3 mb-3"><div class="card card-modern p-3"><div class="stat-icon">🏷️</div><small class="text-muted">Kategori</small><h3 class="fw-bold mb-0" style="color:#6B0F0F;"><?= $total_kategori ?></h3><small>Makanan & Kerajinan</small></div></div>
  </div>

  <div class="row">
    <!-- PESANAN TERBARU -->
    <div class="col-md-7 mb-4">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="judul mb-0">🧾 Pesanan Terbaru</h5>
        <a href="pesanan.php" class="btn btn-emas btn-bulat btn-sm">Lihat Semua →</a>
      </div>
      <div class="card card-modern"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead><tr><th>ID</th><th>Tanggal</th><th>Total</th><th>Status</th></tr></thead>
        <tbody>
        <?php if(mysqli_num_rows($pesanan_terbaru)==0): ?>
          <tr><td colspan="4" class="text-center py-4">Belum ada pesanan.<br><small class="text-muted">Pesanan dari checkout akan muncul di sini</small></td></tr>
        <?php endif; ?>
        <?php while($p=mysqli_fetch_assoc($pesanan_terbaru)): ?>
          <tr>
            <td><b>#PK-<?= str_pad($p['id'],4,'0',STR_PAD_LEFT) ?></b><br><small class="text-muted"><?= $p['jml_item'] ?> item</small></td>
            <td><?= date('d/m/Y H:i', strtotime($p['tanggal'])) ?></td>
            <td class="fw-bold" style="color:#6B0F0F;"><?= rupiah($p['total']) ?></td>
            <td><span class="badge bg-warning text-dark"><?= htmlspecialchars($p['status']) ?></span></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table></div></div>

      <!-- STOK MENIPIS -->
      <h5 class="judul mt-4">⚠️ Stok Menipis / Habis</h5>
      <div class="card card-modern"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead><tr><th>Produk</th><th>Kategori</th><th>Stok</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php if(mysqli_num_rows($produk_stok)==0): ?>
          <tr><td colspan="4" class="text-center py-3">Semua stok aman ✅</td></tr>
        <?php endif; ?>
        <?php while($s=mysqli_fetch_assoc($produk_stok)): ?>
          <tr>
            <td><b><?= htmlspecialchars($s['nama_produk']) ?></b></td>
            <td><span class="badge-kat"><?= htmlspecialchars($s['nama_kategori'] ?? '-') ?></span></td>
            <td><span class="badge bg-danger"><?= $s['stok'] ?> pcs</span></td>
            <td><a href="edit_produk.php?id=<?= $s['id'] ?>" class="btn btn-emas btn-bulat btn-sm">Edit Stok</a></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table></div></div>
    </div>

    <!-- PRODUK TERBARU -->
    <div class="col-md-5 mb-4">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="judul mb-0">✨ Produk Terbaru</h5>
        <a href="produk.php" class="btn btn-merah btn-bulat btn-sm">Kelola →</a>
      </div>
      <div class="card card-modern p-3">
        <?php if(mysqli_num_rows($produk_baru)==0): ?>
          <p class="text-center py-3 mb-0">Belum ada produk.<br><a href="tambah_produk.php">+ Tambah Produk</a></p>
        <?php endif; ?>
        <?php while($pr=mysqli_fetch_assoc($produk_baru)):
          $file = '../uploads/' . $pr['foto'];
          $src  = '../uploads/' . rawurlencode($pr['foto']);
        ?>
        <div class="d-flex gap-2 align-items-center mb-3">
          <?php if(!empty($pr['foto']) && file_exists($file)): ?>
            <img src="<?= $src ?>" class="foto-thumb">
          <?php else: ?>
            <div class="foto-thumb bg-light d-flex align-items-center justify-content-center">🍲</div>
          <?php endif; ?>
          <div class="flex-grow-1">
            <b><?= htmlspecialchars($pr['nama_produk']) ?></b><br>
            <small class="text-muted"><span class="badge-kat"><?= htmlspecialchars($pr['nama_kategori'] ?? '-') ?></span> • Stok: <?= $pr['stok'] ?></small>
          </div>
          <b style="color:#6B0F0F;"><?= rupiah($pr['harga']) ?></b>
        </div>
        <?php endwhile; ?>
      </div>
    </div>
  </div>
</div>

<footer class="text-center py-3" style="background:#6B0F0F; color:#F2D57E;"><small>© 2026 PasarKita • Palembang</small></footer>
</body></html>
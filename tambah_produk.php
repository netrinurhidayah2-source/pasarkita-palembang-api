<?php
include '../config.php';

$pesan = "";
if (isset($_POST['simpan'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $kategori = (int)$_POST['kategori_id'];
    $harga = (int)$_POST['harga'];
    $stok = (int)$_POST['stok'];
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);

    // Upload Foto
    $foto = "";
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $boleh = ['jpg','jpeg','png','webp'];
        if (in_array($ext, $boleh)) {
            $foto = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['foto']['name']);
            move_uploaded_file($_FILES['foto']['tmp_name'], '../uploads/' . $foto);
        } else {
            $pesan = "<div class='alert alert-danger'>Format foto harus JPG / PNG / WEBP</div>";
        }
    }

    if ($pesan == "") {
        $q = mysqli_query($conn, "INSERT INTO produk (nama_produk, kategori_id, harga, stok, deskripsi, foto) VALUES ('$nama', '$kategori', '$harga', '$stok', '$deskripsi', '$foto')");
        if ($q) {
            header("Location: produk.php?sukses=1");
            exit;
        } else {
            // Kalau di tabel kamu kolom deskripsi tidak ada, coba tanpa deskripsi
            $q2 = mysqli_query($conn, "INSERT INTO produk (nama_produk, kategori_id, harga, stok, foto) VALUES ('$nama', '$kategori', '$harga', '$stok', '$foto')");
            if ($q2) { header("Location: produk.php?sukses=1"); exit; }
            $pesan = "<div class='alert alert-danger'>Gagal simpan: " . mysqli_error($conn) . "</div>";
        }
    }
}

$kategori_q = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Produk - PasarKita Admin</title>
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
  .card-modern{ border:none; border-radius:20px; background:white; box-shadow:0 10px 30px rgba(0,0,0,.08); overflow:hidden; border-top:6px solid #C9A227; }
  .hero-admin{ background:linear-gradient(135deg,#6B0F0F,#8B1E1E,#B8860B); color:white; border-radius:20px; padding:24px 28px; border:2px solid #C9A227; }
  .hero-admin h2{ font-weight:900; margin:0; }
  .judul{ font-weight:800; color:#6B0F0F; border-left:6px solid #C9A227; padding-left:12px; }
  .preview-box{ width:100%; height:220px; border:2px dashed #C9A227; border-radius:16px; display:flex; align-items:center; justify-content:center; overflow:hidden; background:#FFF8E7; cursor:pointer; }
  .preview-box img{ width:100%; height:100%; object-fit:cover; }
  label{ font-weight:700; color:#6B0F0F; }
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

<div class="container mt-4 mb-5" style="max-width:900px;">
  <div class="d-flex gap-2 flex-wrap">
    <a href="produk.php" class="btn btn-kembali btn-bulat">← Kembali ke Daftar Produk</a>
    <a href="../index.php" class="btn btn-kembali btn-bulat">🏠 Kembali ke Toko</a>
  </div>

  <div class="hero-admin mt-4"><h2>➕ Tambah Produk Baru</h2><p class="mb-0">Pempek • Tekwan • Kemplang • Songket • Kain Jumputan • Lenggang</p></div>

  <?= $pesan ?>

  <form method="POST" enctype="multipart/form-data">
  <div class="card card-modern mt-4"><div class="card-body p-4">
    <div class="row">
      <!-- KIRI: FOTO -->
      <div class="col-md-4 mb-3">
        <label>Foto Produk *</label>
        <label for="fotoInput" class="preview-box mt-1">
          <span id="previewText" style="font-size:50px;">📷<br><small style="font-size:13px;color:#888;">Klik untuk pilih foto</small></span>
          <img id="previewImg" style="display:none;">
        </label>
        <input type="file" id="fotoInput" name="foto" accept="image/*" class="d-none" onchange="previewFoto(event)" required>
        <small class="text-muted">Format: JPG / PNG / WEBP, Max 2MB. Contoh: foto pempek kapal selam</small>
      </div>

      <!-- KANAN: FORM -->
      <div class="col-md-8">
        <label>Nama Produk *</label>
        <input type="text" name="nama_produk" class="form-control mb-3" placeholder="Contoh: Pempek Kapal Selam" required>

        <div class="row">
          <div class="col-6">
            <label>Kategori *</label>
            <select name="kategori_id" class="form-select mb-3" required>
              <option value="">-- Pilih Kategori --</option>
              <?php while($k = mysqli_fetch_assoc($kategori_q)): ?>
                <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-6">
            <label>Stok *</label>
            <input type="number" name="stok" class="form-control mb-3" placeholder="Contoh: 50" min="0" required>
          </div>
        </div>

        <label>Harga (Rp) *</label>
        <input type="number" name="harga" class="form-control mb-3" placeholder="Contoh: 25000" min="0" required>

        <label>Deskripsi Produk</label>
        <textarea name="deskripsi" class="form-control" rows="3" placeholder="Contoh: Pempek asli Palembang, ikan tenggiri pilihan, cuko kental pedas manis..."></textarea>
      </div>
    </div>

    <hr>
    <div class="d-flex gap-2">
      <button type="submit" name="simpan" class="btn btn-tambah btn-bulat flex-grow-1 py-2">✅ Simpan Produk</button>
      <a href="produk.php" class="btn btn-kembali btn-bulat">Batal</a>
    </div>
  </div></div>
  </form>
</div>

<footer class="text-center py-3" style="background:#6B0F0F; color:#F2D57E;"><small>© 2026 PasarKita • Palembang</small></footer>

<script>
function previewFoto(e){
  const file = e.target.files[0];
  if(file){
    const reader = new FileReader();
    reader.onload = function(ev){
      document.getElementById('previewImg').src = ev.target.result;
      document.getElementById('previewImg').style.display = 'block';
      document.getElementById('previewText').style.display = 'none';
    }
    reader.readAsDataURL(file);
  }
}
</script>
</body></html>
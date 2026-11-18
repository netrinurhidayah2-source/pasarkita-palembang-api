<?php
include '../config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: produk.php");
    exit;
}

// Ambil data produk lama
$q = mysqli_query($conn, "SELECT * FROM produk WHERE id = $id");
$produk = mysqli_fetch_assoc($q);
if (!$produk) {
    echo "<script>alert('Produk tidak ditemukan!'); window.location='produk.php';</script>";
    exit;
}

// Ambil kategori untuk dropdown
$kategori = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori ASC");

// --- PROSES UPDATE ---
if (isset($_POST['update'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $kategori_id = (int)$_POST['kategori_id'];
    $harga = (int)$_POST['harga'];
    $stok = (int)$_POST['stok'];
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $foto_lama = $produk['foto'];
    $foto_baru = $foto_lama;

    // Jika ada foto baru diupload
    if (!empty($_FILES['foto']['name'])) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];
        if (in_array($ext, $allowed)) {
            $foto_baru = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['foto']['name']);
            move_uploaded_file($_FILES['foto']['tmp_name'], '../uploads/' . $foto_baru);
            // Hapus foto lama biar gak numpuk
            if (!empty($foto_lama) && file_exists('../uploads/' . $foto_lama)) {
                unlink('../uploads/' . $foto_lama);
            }
        }
    }

    $sql = "UPDATE produk SET
            nama_produk='$nama',
            kategori_id=$kategori_id,
            harga=$harga,
            stok=$stok,
            deskripsi='$deskripsi',
            foto='" . mysqli_real_escape_string($conn, $foto_baru) . "'
            WHERE id=$id";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Produk berhasil diupdate!'); window.location='produk.php';</script>";
        exit;
    } else {
        $error = "Gagal update: " . mysqli_error($conn);
    }
}

// Untuk preview
$foto_path = '../uploads/' . $produk['foto'];
$foto_src = '../uploads/' . rawurlencode($produk['foto']);
?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Produk - PasarKita Admin</title>
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
  .btn-simpan{ background:#0F9D58; color:white; } .btn-simpan:hover{ background:#0B7A44; color:white; }
  .card-modern{ border:none; border-radius:20px; background:white; box-shadow:0 10px 30px rgba(0,0,0,.08); overflow:hidden; }
  .hero-admin{ background:linear-gradient(135deg,#6B0F0F,#8B1E1E,#B8860B); color:white; border-radius:20px; padding:24px 28px; border:2px solid #C9A227; }
  .hero-admin h2{ font-weight:900; margin:0; }
  .judul{ font-weight:800; color:#6B0F0F; border-left:6px solid #C9A227; padding-left:12px; }
  .form-control, .form-select{ border-radius:12px; padding:11px 14px; border:1.5px solid #E8D9A8; }
  .form-control:focus, .form-select:focus{ border-color:#C9A227; box-shadow:0 0 0 3px rgba(201,162,39,.2); }
  .preview-box{ border:2px dashed #C9A227; border-radius:18px; background:#FFFDF5; min-height:280px; display:flex; align-items:center; justify-content:center; overflow:hidden; cursor:pointer; position:relative; }
  .preview-box img{ width:100%; height:280px; object-fit:cover; }
  .preview-box .placeholder{ text-align:center; color:#8B1E1E; }
  .badge-kat{ background:#FFF3CD; color:#8B1E1E; padding:4px 10px; border-radius:20px; font-weight:700; font-size:11px; border:1px solid #C9A227; }
  .badge-id{ background:#6B0F0F; color:#F2D57E; padding:4px 10px; border-radius:20px; font-weight:700; font-size:11px; }
  .foto-thumb-preview{ width:100%; height:280px; object-fit:cover; }
</style>
</head>
<body>
<nav class="navbar navbar-dark navbar-modern"><div class="container">
<a class="navbar-brand" href="../index.php">🥬 Pasar<span>Kita</span> <small style="font-size:12px;color:#F2D57E;">Admin</small></a>
<div class="d-flex gap-2">
  <a href="dashboard.php" class="btn btn-warning btn-bulat btn-sm fw-bold">← Dashboard</a>
  <a href="produk.php" class="btn btn-light btn-bulat btn-sm">📦 Daftar Produk</a>
</div>
</div></nav>

<div class="container mt-4 mb-5">
  <div class="hero-admin d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <h2>✏️ Edit Produk</h2>
      <p class="mb-0">Ubah data <b><?= htmlspecialchars($produk['nama_produk']) ?></b> • Preview langsung berubah sebelum disimpan</p>
    </div>
    <a href="produk.php" class="btn btn-light btn-bulat">← Batal</a>
  </div>

  <?php if(isset($error)): ?>
    <div class="alert alert-danger mt-3 rounded-4"><?= $error ?></div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data" class="row mt-4">
    <!-- KIRI: PREVIEW LIVE -->
    <div class="col-md-5 mb-4">
      <h5 class="judul">👀 Live Preview</h5>
      <div class="card card-modern p-3">
        <label for="fotoInput" class="preview-box mb-3">
          <?php if(!empty($produk['foto']) && file_exists($foto_path)): ?>
            <img id="previewImg" src="<?= $foto_src ?>" class="foto-thumb-preview">
            <div id="previewPlaceholder" class="placeholder" style="display:none;">
              <div style="font-size:48px;">📸</div>
              <b>Klik untuk Ganti Foto</b><br><small>JPG, PNG, WEBP • Maks 2MB</small>
            </div>
          <?php else: ?>
            <img id="previewImg" src="" style="display:none;">
            <div id="previewPlaceholder" class="placeholder">
              <div style="font-size:48px;">🍲</div>
              <b id="previewNama"> <?= htmlspecialchars($produk['nama_produk']) ?></b><br><small>Klik untuk Ganti Foto</small>
            </div>
          <?php endif; ?>
          <div style="position:absolute; bottom:10px; right:10px;" class="badge bg-dark">📷 Ganti Foto</div>
        </label>
        <input type="file" name="foto" id="fotoInput" accept=".jpg,.jpeg,.png,.webp" hidden>

        <!-- Card Preview Produk kayak di Beranda -->
        <div class="d-flex gap-2 align-items-center">
          <div class="flex-grow-1">
            <span class="badge-kat" id="previewKategori">
              <?php
                // Ambil nama kategori produk ini untuk preview awal
                $kq = mysqli_query($conn, "SELECT nama_kategori FROM kategori WHERE id=".(int)$produk['kategori_id']);
                $kr = mysqli_fetch_assoc($kq);
                echo htmlspecialchars($kr['nama_kategori'] ?? 'Kategori');
              ?>
            </span>
            <span class="badge-id">#<?= $produk['id'] ?></span><br>
            <b id="previewNama2" style="font-size:18px; color:#6B0F0F;"><?= htmlspecialchars($produk['nama_produk']) ?></b><br>
            <small class="text-muted">Stok: <b id="previewStok"><?= $produk['stok'] ?></b> pcs</small>
          </div>
          <div class="text-end">
            <b id="previewHarga" style="color:#6B0F0F; font-size:18px;"><?= rupiah($produk['harga']) ?></b><br>
            <small>⭐ 4.9 • Asli Palembang</small>
          </div>
        </div>
        <p class="text-muted mt-2 mb-0" id="previewDeskripsi" style="font-size:13px;"><?= htmlspecialchars($produk['deskripsi']) ?></p>
        <div class="alert alert-warning mt-3 mb-0 rounded-4" style="font-size:13px;">
          💡 <b>Tips:</b> Kalau tidak mau ganti foto, kosongkan saja input foto. Foto lama akan tetap dipakai.
        </div>
      </div>
    </div>

    <!-- KANAN: FORM EDIT -->
    <div class="col-md-7 mb-4">
      <h5 class="judul">📝 Form Edit Produk</h5>
      <div class="card card-modern p-4">
        <div class="mb-3">
          <label class="fw-bold" style="color:#6B0F0F;">Nama Produk *</label>
          <input type="text" name="nama_produk" id="inputNama" class="form-control" value="<?= htmlspecialchars($produk['nama_produk']) ?>" required placeholder="Contoh: Pempek Kapal Selam">
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="fw-bold" style="color:#6B0F0F;">Kategori *</label>
            <select name="kategori_id" id="inputKategori" class="form-select" required>
              <option value="">-- Pilih Kategori --</option>
              <?php while($k=mysqli_fetch_assoc($kategori)): ?>
                <option value="<?= $k['id'] ?>" <?= $k['id']==$produk['kategori_id']?'selected':'' ?>><?= htmlspecialchars($k['nama_kategori']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-6 mb-3">
            <label class="fw-bold" style="color:#6B0F0F;">Stok *</label>
            <input type="number" name="stok" id="inputStok" class="form-control" min="0" value="<?= $produk['stok'] ?>" required>
          </div>
        </div>

        <div class="mb-3">
          <label class="fw-bold" style="color:#6B0F0F;">Harga (Rp) *</label>
          <input type="number" name="harga" id="inputHarga" class="form-control" min="0" value="<?= $produk['harga'] ?>" required>
          <small class="text-muted">Preview harga: <b id="hargaText"><?= rupiah($produk['harga']) ?></b></small>
        </div>

        <div class="mb-3">
          <label class="fw-bold" style="color:#6B0F0F;">Deskripsi</label>
          <textarea name="deskripsi" id="inputDeskripsi" class="form-control" rows="4" placeholder="Ceritakan produknya, bahan, rasa, dll..."><?= htmlspecialchars($produk['deskripsi']) ?></textarea>
        </div>

        <div class="mb-3">
          <label class="fw-bold" style="color:#6B0F0F;">Ganti Foto Produk (Opsional)</label>
          <input type="file" name="foto" id="fotoInput2" accept=".jpg,.jpeg,.png,.webp" class="form-control">
          <small class="text-muted">Format JPG, PNG, WEBP • Maks 2MB • Kosongkan kalau tidak mau ganti foto. Foto lama: <b><?= htmlspecialchars($produk['foto']) ?></b></small>
        </div>

        <div class="d-flex gap-2 flex-wrap">
          <button type="submit" name="update" class="btn btn-simpan btn-bulat flex-grow-1">💾 Simpan Perubahan</button>
          <a href="produk.php" class="btn btn-kembali btn-bulat">Batal</a>
          <a href="dashboard.php" class="btn btn-emas btn-bulat">← Dashboard</a>
        </div>
      </div>
    </div>
  </form>
</div>

<footer class="text-center py-3" style="background:#6B0F0F; color:#F2D57E;"><small>© 2026 PasarKita • Palembang</small></footer>

<script>
  // Live Preview: Nama, Harga, Stok, Deskripsi, Kategori
  const inputNama = document.getElementById('inputNama');
  const inputHarga = document.getElementById('inputHarga');
  const inputStok = document.getElementById('inputStok');
  const inputDeskripsi = document.getElementById('inputDeskripsi');
  const inputKategori = document.getElementById('inputKategori');

  inputNama.addEventListener('input', () => {
    document.getElementById('previewNama2').textContent = inputNama.value || 'Nama Produk';
    let p = document.getElementById('previewNama'); if(p) p.textContent = inputNama.value;
  });
  inputHarga.addEventListener('input', () => {
    let v = parseInt(inputHarga.value) || 0;
    let f = 'Rp ' + v.toLocaleString('id-ID');
    document.getElementById('previewHarga').textContent = f;
    document.getElementById('hargaText').textContent = f;
  });
  inputStok.addEventListener('input', () => {
    document.getElementById('previewStok').textContent = inputStok.value || 0;
  });
  inputDeskripsi.addEventListener('input', () => {
    document.getElementById('previewDeskripsi').textContent = inputDeskripsi.value || 'Deskripsi produk akan muncul di sini...';
  });
  inputKategori.addEventListener('change', () => {
    let t = inputKategori.options[inputKategori.selectedIndex]?.text || 'Kategori';
    document.getElementById('previewKategori').textContent = t;
  });

  // Preview Foto - 2 input disinkronkan
  const fotoInput = document.getElementById('fotoInput');
  const fotoInput2 = document.getElementById('fotoInput2');
  function previewFoto(input){
    if(input.files && input.files[0]){
      let reader = new FileReader();
      reader.onload = function(e){
        let img = document.getElementById('previewImg');
        img.src = e.target.result;
        img.style.display = 'block';
        let ph = document.getElementById('previewPlaceholder');
        if(ph) ph.style.display = 'none';
      };
      reader.readAsDataURL(input.files[0]);
    }
  }
  fotoInput.addEventListener('change', () => previewFoto(fotoInput));
  fotoInput2.addEventListener('change', function(){
    // samakan ke input utama biar yang dikirim cuma satu
    let dt = new DataTransfer();
    dt.items.add(this.files[0]);
    fotoInput.files = dt.files;
    previewFoto(this);
  });
</script>
</body></html>
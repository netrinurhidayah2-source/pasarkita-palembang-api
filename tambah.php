<?php include '../config.php';
if(isset($_POST['simpan'])){
  $kat = $_POST['category_id']; $nama = mysqli_real_escape_string($conn, $_POST['nama_produk']);
  $desk = mysqli_real_escape_string($conn, $_POST['deskripsi']); $harga = (int)$_POST['harga']; $stok = (int)$_POST['stok'];
  mysqli_query($conn, "INSERT INTO products (category_id, seller_id, nama_produk, deskripsi, harga, stok) VALUES ('$kat', 1, '$nama', '$desk', '$harga', '$stok')");
  header("Location: produk.php"); exit;
}
?>
<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Tambah Produk</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body><div class="container mt-4" style="max-width:600px">
<h3>Tambah Produk</h3>
<form method="POST" class="card p-4">
  <label>Kategori</label>
  <select name="category_id" class="form-select mb-2"><?php $k=mysqli_query($conn,"SELECT * FROM categories"); while($r=mysqli_fetch_assoc($k)) echo "<option value='{$r['id']}'>{$r['nama_kategori']}</option>"; ?></select>
  <label>Nama Produk</label><input name="nama_produk" class="form-control mb-2" required>
  <label>Deskripsi</label><textarea name="deskripsi" class="form-control mb-2" required></textarea>
  <label>Harga</label><input name="harga" type="number" class="form-control mb-2" required>
  <label>Stok</label><input name="stok" type="number" class="form-control mb-3" required>
  <button name="simpan" class="btn btn-success">Simpan</button>
  <a href="produk.php" class="btn btn-secondary mt-2">Batal</a>
</form></div></body></html>
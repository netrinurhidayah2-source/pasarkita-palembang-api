<?php include '../config.php';
$id = (int)$_GET['id'];
$q = mysqli_query($conn, "SELECT * FROM products WHERE id='$id'");
$p = mysqli_fetch_assoc($q);
if(isset($_POST['update'])){
  $kat = $_POST['category_id']; $nama = mysqli_real_escape_string($conn, $_POST['nama_produk']);
  $desk = mysqli_real_escape_string($conn, $_POST['deskripsi']); $harga = (int)$_POST['harga']; $stok = (int)$_POST['stok'];
  mysqli_query($conn, "UPDATE products SET category_id='$kat', nama_produk='$nama', deskripsi='$desk', harga='$harga', stok='$stok' WHERE id='$id'");
  header("Location: produk.php"); exit;
}
?>
<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Edit Produk</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body><div class="container mt-4" style="max-width:600px">
<h3>Edit Produk: <?= $p['nama_produk'] ?></h3>
<form method="POST" class="card p-4">
  <label>Kategori</label>
  <select name="category_id" class="form-select mb-2"><?php $k=mysqli_query($conn,"SELECT * FROM categories"); while($r=mysqli_fetch_assoc($k)){ $sel=$r['id']==$p['category_id']?'selected':''; echo "<option value='{$r['id']}' $sel>{$r['nama_kategori']}</option>"; } ?></select>
  <label>Nama Produk</label><input name="nama_produk" value="<?= $p['nama_produk'] ?>" class="form-control mb-2" required>
  <label>Deskripsi</label><textarea name="deskripsi" class="form-control mb-2" required><?= $p['deskripsi'] ?></textarea>
  <label>Harga</label><input name="harga" type="number" value="<?= $p['harga'] ?>" class="form-control mb-2" required>
  <label>Stok</label><input name="stok" type="number" value="<?= $p['stok'] ?>" class="form-control mb-3" required>
  <button name="update" class="btn btn-warning">Update</button>
  <a href="produk.php" class="btn btn-secondary mt-2">Batal</a>
</form></div></body></html>
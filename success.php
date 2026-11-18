<?php include 'config.php';
$id=(int)($_GET['id']??0); $q=mysqli_query($conn,"SELECT * FROM orders WHERE id='$id'"); $o=mysqli_fetch_assoc($q); if(!$o){ header("Location: index.php"); exit; }
?>
<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Pesanan Berhasil</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body><div class="container mt-5 text-center">
<h1 class="text-success">Pesanan Berhasil!</h1><p>Terima kasih <b><?= $o['nama_pembeli'] ?></b></p>
<div class="card p-4 mx-auto" style="max-width:500px"><p>Nomor Pesanan: <b>#<?= $o['id'] ?></b></p><p>Total: <b><?= rupiah($o['total_harga']) ?></b></p><p>Alamat: <?= $o['alamat_kirim'] ?></p><p>Status: <span class="badge bg-warning text-dark"><?= $o['status'] ?></span></p></div>
<a href="index.php" class="btn btn-success mt-4">← Kembali ke Beranda</a></div></body></html>
<?php
include '../config.php';
$id = (int)$_GET['id'];
mysqli_query($conn, "DELETE FROM products WHERE id='$id'");
header("Location: produk.php");
exit;
?>
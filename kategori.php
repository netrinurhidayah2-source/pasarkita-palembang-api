
<?php
require_once "bantuan.php";

cek_method("GET");

try {
    $query = mysqli_query($conn, "
        SELECT id, nama_kategori, deskripsi
        FROM kategori
        ORDER BY id ASC
    ");

    $kategori = [];

    while ($row = mysqli_fetch_assoc($query)) {
        $kategori[] = $row;
    }

    response_json(true, "Data kategori berhasil diambil", [
        "jumlah" => count($kategori),
        "kategori" => $kategori
    ]);

} catch (Throwable $e) {
    response_json(false, "Gagal mengambil data kategori", null, 500);
}
?>

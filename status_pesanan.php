
<?php
require_once "bantuan.php";

// API hanya menerima metode PUT
cek_method("PUT");

// Membaca data JSON dari Postman
$data = baca_json();

$pesanan_id = isset($data["pesanan_id"])
    ? (int) $data["pesanan_id"]
    : 0;

$status = isset($data["status"])
    ? trim($data["status"])
    : "";

// Validasi input
if ($pesanan_id < 1) {
    response_json(false, "ID pesanan wajib diisi", null, 400);
}

// Status yang diperbolehkan
$status_valid = [
    "Menunggu Pembayaran",
    "Diproses",
    "Selesai",
    "Dibatalkan"
];

if (!in_array($status, $status_valid, true)) {
    response_json(false, "Status pesanan tidak valid", [
        "status_yang_diizinkan" => $status_valid
    ], 400);
}

try {
    // Periksa apakah pesanan tersedia
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id FROM pesanan WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $pesanan_id);
    mysqli_stmt_execute($stmt);

    $hasil = mysqli_stmt_get_result($stmt);
    $pesanan = mysqli_fetch_assoc($hasil);
    mysqli_stmt_close($stmt);

    if (!$pesanan) {
        response_json(false, "Pesanan tidak ditemukan", null, 404);
    }

    // Perbarui status pesanan
    $stmtUpdate = mysqli_prepare(
        $conn,
        "UPDATE pesanan SET status = ? WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $stmtUpdate,
        "si",
        $status,
        $pesanan_id
    );

    mysqli_stmt_execute($stmtUpdate);
    mysqli_stmt_close($stmtUpdate);

    response_json(true, "Status pesanan berhasil diperbarui", [
        "pesanan_id" => $pesanan_id,
        "status_baru" => $status
    ]);

} catch (Throwable $e) {
    response_json(false, "Gagal memperbarui status pesanan", null, 500);
}
?>

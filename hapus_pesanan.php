
<?php
require_once "bantuan.php";

// API hanya menerima DELETE
cek_method("DELETE");

// Ambil data JSON
$data = baca_json();

$pesanan_id = isset($data["pesanan_id"])
    ? filter_var($data["pesanan_id"], FILTER_VALIDATE_INT)
    : false;

// Validasi ID
if (!$pesanan_id || $pesanan_id < 1) {
    response_json(false, "pesanan_id wajib diisi dengan angka yang valid", null, 400);
}

try {
    mysqli_begin_transaction($conn);

    // Periksa apakah pesanan tersedia
    $stmtCek = mysqli_prepare(
        $conn,
        "SELECT id FROM pesanan WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmtCek, "i", $pesanan_id);
    mysqli_stmt_execute($stmtCek);

    $hasil = mysqli_stmt_get_result($stmtCek);
    $pesanan = mysqli_fetch_assoc($hasil);

    mysqli_stmt_close($stmtCek);

    if (!$pesanan) {
        mysqli_rollback($conn);
        response_json(false, "Pesanan tidak ditemukan", null, 404);
    }

    // Hapus detail pesanan terlebih dahulu
    $stmtDetail = mysqli_prepare(
        $conn,
        "DELETE FROM pesanan_detail WHERE pesanan_id = ?"
    );

    mysqli_stmt_bind_param($stmtDetail, "i", $pesanan_id);
    mysqli_stmt_execute($stmtDetail);
    mysqli_stmt_close($stmtDetail);

    // Hapus pesanan utama
    $stmtPesanan = mysqli_prepare(
        $conn,
        "DELETE FROM pesanan WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmtPesanan, "i", $pesanan_id);
    mysqli_stmt_execute($stmtPesanan);

    mysqli_stmt_close($stmtPesanan);

    mysqli_commit($conn);

    response_json(true, "Pesanan berhasil dihapus", [
        "pesanan_id" => (int) $pesanan_id
    ]);

} catch (Throwable $e) {
    mysqli_rollback($conn);

    response_json(false, "Gagal menghapus pesanan", null, 500);
}
?>

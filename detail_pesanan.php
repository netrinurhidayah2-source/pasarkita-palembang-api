
<?php
require_once "bantuan.php";

// Hanya menerima GET
cek_method("GET");

// Ambil ID pesanan dari URL
$pesanan_id = isset($_GET["pesanan_id"])
    ? filter_var($_GET["pesanan_id"], FILTER_VALIDATE_INT)
    : false;

// Validasi ID
if (!$pesanan_id || $pesanan_id < 1) {
    response_json(false, "pesanan_id wajib diisi dengan angka yang valid", null, 400);
}

try {
    // Ambil informasi utama pesanan
    $sql = "
        SELECT
            ps.id AS pesanan_id,
            ps.pengguna_id,
            pg.nama AS nama_pelanggan,
            ps.total,
            ps.tanggal,
            ps.status
        FROM pesanan ps
        LEFT JOIN pengguna pg ON ps.pengguna_id = pg.id
        WHERE ps.id = ?
    ";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $pesanan_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $pesanan = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$pesanan) {
        response_json(false, "Pesanan tidak ditemukan", null, 404);
    }

    // Ambil detail produk pesanan
    $sqlDetail = "
        SELECT
            pd.id AS detail_id,
            pd.produk_id,
            pr.nama_produk,
            pd.jumlah,
            pd.harga,
            (pd.jumlah * pd.harga) AS subtotal
        FROM pesanan_detail pd
        LEFT JOIN produk pr ON pd.produk_id = pr.id
        WHERE pd.pesanan_id = ?
        ORDER BY pd.id ASC
    ";

    $stmtDetail = mysqli_prepare($conn, $sqlDetail);
    mysqli_stmt_bind_param($stmtDetail, "i", $pesanan_id);
    mysqli_stmt_execute($stmtDetail);

    $resultDetail = mysqli_stmt_get_result($stmtDetail);

    $detail = [];

    while ($row = mysqli_fetch_assoc($resultDetail)) {
        $detail[] = [
            "detail_id" => (int) $row["detail_id"],
            "produk_id" => (int) $row["produk_id"],
            "nama_produk" => $row["nama_produk"],
            "jumlah" => (int) $row["jumlah"],
            "harga" => (int) $row["harga"],
            "subtotal" => (int) $row["subtotal"]
        ];
    }

    mysqli_stmt_close($stmtDetail);

    // Gabungkan data pesanan dan detail
    $pesanan["pesanan_id"] = (int) $pesanan["pesanan_id"];
    $pesanan["pengguna_id"] = (int) $pesanan["pengguna_id"];
    $pesanan["total"] = (int) $pesanan["total"];
    $pesanan["detail"] = $detail;

    response_json(true, "Detail pesanan berhasil diambil", $pesanan);

} catch (Throwable $e) {
    response_json(false, "Gagal mengambil detail pesanan", null, 500);
}
?>

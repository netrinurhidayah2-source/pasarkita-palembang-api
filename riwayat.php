
<?php
require_once "bantuan.php";

// API ini hanya menerima metode GET
cek_method("GET");

// Ambil ID pengguna dari URL
$pengguna_id = isset($_GET["pengguna_id"])
    ? filter_var($_GET["pengguna_id"], FILTER_VALIDATE_INT)
    : false;

// Validasi ID pengguna
if (!$pengguna_id || $pengguna_id < 1) {
    response_json(false, "pengguna_id wajib diisi dengan angka yang valid", null, 400);
}

try {
    // Ambil data pesanan beserta detail produk
    $sql = "
        SELECT
            ps.id AS pesanan_id,
            ps.pengguna_id,
            ps.total,
            ps.tanggal,
            ps.status,
            pd.id AS detail_id,
            pd.produk_id,
            pd.jumlah,
            pd.harga,
            pr.nama_produk
        FROM pesanan ps
        LEFT JOIN pesanan_detail pd
            ON ps.id = pd.pesanan_id
        LEFT JOIN produk pr
            ON pd.produk_id = pr.id
        WHERE ps.pengguna_id = ?
        ORDER BY ps.id DESC, pd.id ASC
    ";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        throw new Exception("Query database gagal");
    }

    mysqli_stmt_bind_param($stmt, "i", $pengguna_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $pesanan = [];

    // Gabungkan detail produk ke masing-masing pesanan
    while ($row = mysqli_fetch_assoc($result)) {
        $id = $row["pesanan_id"];

        if (!isset($pesanan[$id])) {
            $pesanan[$id] = [
                "pesanan_id" => (int) $row["pesanan_id"],
                "pengguna_id" => (int) $row["pengguna_id"],
                "total" => (int) $row["total"],
                "tanggal" => $row["tanggal"],
                "status" => $row["status"],
                "detail" => []
            ];
        }

        // Pesanan bisa saja belum memiliki detail
        if ($row["detail_id"] !== null) {
            $pesanan[$id]["detail"][] = [
                "detail_id" => (int) $row["detail_id"],
                "produk_id" => (int) $row["produk_id"],
                "nama_produk" => $row["nama_produk"],
                "jumlah" => (int) $row["jumlah"],
                "harga" => (int) $row["harga"],
                "subtotal" => (int) $row["harga"] * (int) $row["jumlah"]
            ];
        }
    }

    mysqli_stmt_close($stmt);

    // Ubah hasil menjadi array JSON
    $data = array_values($pesanan);

    response_json(true, "Riwayat pesanan berhasil diambil", [
        "pengguna_id" => (int) $pengguna_id,
        "jumlah_pesanan" => count($data),
        "pesanan" => $data
    ]);

} catch (Throwable $e) {
    response_json(false, "Gagal mengambil riwayat pesanan", null, 500);
}
?>

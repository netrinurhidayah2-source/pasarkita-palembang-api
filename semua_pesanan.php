
<?php
require_once "bantuan.php";

// API hanya menerima GET
cek_method("GET");

try {
    // Mengambil seluruh pesanan dan nama pelanggan
    $sql = "
        SELECT
            ps.id AS pesanan_id,
            ps.pengguna_id,
            pg.nama AS nama_pelanggan,
            ps.total,
            ps.tanggal,
            ps.status
        FROM pesanan ps
        LEFT JOIN pengguna pg
            ON ps.pengguna_id = pg.id
        ORDER BY ps.id DESC
    ";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        throw new Exception("Gagal mengambil data pesanan");
    }

    $daftar_pesanan = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $pesanan_id = (int) $row["pesanan_id"];

        // Ambil rincian produk untuk setiap pesanan
        $sqlDetail = "
            SELECT
                pd.produk_id,
                pr.nama_produk,
                pd.jumlah,
                pd.harga,
                (pd.jumlah * pd.harga) AS subtotal
            FROM pesanan_detail pd
            LEFT JOIN produk pr
                ON pd.produk_id = pr.id
            WHERE pd.pesanan_id = ?
        ";

        $stmt = mysqli_prepare($conn, $sqlDetail);
        mysqli_stmt_bind_param($stmt, "i", $pesanan_id);
        mysqli_stmt_execute($stmt);

        $detailResult = mysqli_stmt_get_result($stmt);
        $detail = [];

        while ($item = mysqli_fetch_assoc($detailResult)) {
            $detail[] = [
                "produk_id" => (int) $item["produk_id"],
                "nama_produk" => $item["nama_produk"],
                "jumlah" => (int) $item["jumlah"],
                "harga" => (int) $item["harga"],
                "subtotal" => (int) $item["subtotal"]
            ];
        }

        mysqli_stmt_close($stmt);

        $daftar_pesanan[] = [
            "pesanan_id" => $pesanan_id,
            "pengguna_id" => $row["pengguna_id"] !== null
                ? (int) $row["pengguna_id"] : null,
            "nama_pelanggan" => $row["nama_pelanggan"],
            "total" => (int) $row["total"],
            "tanggal" => $row["tanggal"],
            "status" => $row["status"],
            "detail" => $detail
        ];
    }

    response_json(true, "Seluruh data pesanan berhasil diambil", [
        "jumlah_pesanan" => count($daftar_pesanan),
        "pesanan" => $daftar_pesanan
    ]);

} catch (Throwable $e) {
    response_json(false, "Gagal mengambil seluruh data pesanan", null, 500);
}
?>

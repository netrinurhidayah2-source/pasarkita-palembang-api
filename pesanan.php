
<?php
require_once "bantuan.php";

cek_method("POST");

$data = baca_json();

$pengguna_id = isset($data["pengguna_id"])
    ? (int) $data["pengguna_id"]
    : 0;

$items = $data["items"] ?? [];

if ($pengguna_id < 1) {
    response_json(false, "pengguna_id wajib diisi", null, 400);
}

if (!is_array($items) || count($items) < 1) {
    response_json(false, "Daftar produk pesanan masih kosong", null, 400);
}

try {
    mysqli_begin_transaction($conn);

    // Periksa pengguna
    $stmtUser = mysqli_prepare(
        $conn,
        "SELECT id FROM pengguna WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmtUser, "i", $pengguna_id);
    mysqli_stmt_execute($stmtUser);

    $userResult = mysqli_stmt_get_result($stmtUser);

    if (mysqli_num_rows($userResult) === 0) {
        throw new Exception("Pengguna tidak ditemukan");
    }

    $daftar = [];
    $total = 0;

    // Periksa produk dan stok
    foreach ($items as $item) {
        $produk_id = isset($item["produk_id"])
            ? (int) $item["produk_id"]
            : 0;

        $jumlah = isset($item["jumlah"])
            ? (int) $item["jumlah"]
            : 0;

        if ($produk_id < 1 || $jumlah < 1) {
            throw new Exception("ID produk atau jumlah tidak valid");
        }

        $stmtProduk = mysqli_prepare($conn, "
            SELECT id, nama_produk, harga, stok
            FROM produk
            WHERE id = ?
            FOR UPDATE
        ");

        mysqli_stmt_bind_param($stmtProduk, "i", $produk_id);
        mysqli_stmt_execute($stmtProduk);

        $hasil = mysqli_stmt_get_result($stmtProduk);
        $produk = mysqli_fetch_assoc($hasil);

        if (!$produk) {
            throw new Exception("Produk ID $produk_id tidak ditemukan");
        }

        if ($jumlah > (int)$produk["stok"]) {
            throw new Exception(
                "Stok " . $produk["nama_produk"] . " tidak mencukupi"
            );
        }

        $harga = (int)$produk["harga"];
        $subtotal = $harga * $jumlah;

        $total += $subtotal;

        $daftar[] = [
            "produk_id" => $produk_id,
            "jumlah" => $jumlah,
            "harga" => $harga,
            "subtotal" => $subtotal
        ];
    }

    // Simpan pesanan
    $status = "Menunggu Pembayaran";

    $stmtPesanan = mysqli_prepare($conn, "
        INSERT INTO pesanan
        (pengguna_id, total, tanggal, status)
        VALUES (?, ?, NOW(), ?)
    ");

    mysqli_stmt_bind_param(
        $stmtPesanan,
        "iis",
        $pengguna_id,
        $total,
        $status
    );

    mysqli_stmt_execute($stmtPesanan);

    $pesanan_id = mysqli_insert_id($conn);

    // Simpan detail dan kurangi stok
    $stmtDetail = mysqli_prepare($conn, "
        INSERT INTO pesanan_detail
        (pesanan_id, produk_id, jumlah, harga)
        VALUES (?, ?, ?, ?)
    ");

    $stmtStok = mysqli_prepare($conn, "
        UPDATE produk
        SET stok = stok - ?
        WHERE id = ? AND stok >= ?
    ");

    foreach ($daftar as $item) {
        $produk_id = $item["produk_id"];
        $jumlah = $item["jumlah"];
        $harga = $item["harga"];

        mysqli_stmt_bind_param(
            $stmtDetail,
            "iiii",
            $pesanan_id,
            $produk_id,
            $jumlah,
            $harga
        );

        mysqli_stmt_execute($stmtDetail);

        mysqli_stmt_bind_param(
            $stmtStok,
            "iii",
            $jumlah,
            $produk_id,
            $jumlah
        );

        mysqli_stmt_execute($stmtStok);

        if (mysqli_stmt_affected_rows($stmtStok) !== 1) {
            throw new Exception("Gagal memperbarui stok produk");
        }
    }

    mysqli_commit($conn);

    response_json(true, "Pesanan berhasil dibuat", [
        "pesanan_id" => $pesanan_id,
        "pengguna_id" => $pengguna_id,
        "total" => $total,
        "status" => $status,
        "detail" => $daftar
    ], 201);

} catch (Throwable $e) {
    mysqli_rollback($conn);

    $pesan = $e->getMessage();

    $kode = (
        $pesan === "Pengguna tidak ditemukan" ||
        strpos($pesan, "tidak ditemukan") !== false
    ) ? 404 : 400;

    response_json(false, $pesan, null, $kode);
}
?>

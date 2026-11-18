
<?php
require_once "bantuan.php";
cek_method("GET");

try {

    // 1. Jika ada ID, tampilkan detail satu produk
    if (isset($_GET["id"])) {

        $id = filter_var($_GET["id"], FILTER_VALIDATE_INT);

        if (!$id || $id < 1) {
            response_json(false, "ID produk tidak valid", null, 400);
        }

        $stmt = mysqli_prepare($conn, "
            SELECT p.id, p.nama_produk, p.harga, p.stok,
                   p.deskripsi, p.foto, k.nama_kategori
            FROM produk p
            LEFT JOIN kategori k ON p.kategori_id = k.id
            WHERE p.id = ?
        ");

        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $produk = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$produk) {
            response_json(false, "Produk tidak ditemukan", null, 404);
        }

        response_json(true, "Detail produk berhasil diambil", $produk);
    }

    // 2. Ambil parameter pencarian dan kategori
    $keyword = trim($_GET["q"] ?? "");
    $kategori_id = null;

    if (isset($_GET["kategori_id"]) && $_GET["kategori_id"] !== "") {
        $kategori_id = filter_var(
            $_GET["kategori_id"],
            FILTER_VALIDATE_INT
        );

        if (!$kategori_id || $kategori_id < 1) {
            response_json(false, "ID kategori tidak valid", null, 400);
        }
    }

    // 3. Susun query sesuai filter
    $sql = "
        SELECT p.id, p.nama_produk, p.harga, p.stok,
               p.deskripsi, p.foto, k.nama_kategori
        FROM produk p
        LEFT JOIN kategori k ON p.kategori_id = k.id
    ";

    if ($keyword !== "" && $kategori_id !== null) {
        $sql .= " WHERE p.nama_produk LIKE ? AND p.kategori_id = ?";
    } elseif ($keyword !== "") {
        $sql .= " WHERE p.nama_produk LIKE ?";
    } elseif ($kategori_id !== null) {
        $sql .= " WHERE p.kategori_id = ?";
    }

    $sql .= " ORDER BY p.id ASC";

    $stmt = mysqli_prepare($conn, $sql);

    // 4. Kirim parameter ke query
    if ($keyword !== "" && $kategori_id !== null) {
        $pencarian = "%" . $keyword . "%";
        mysqli_stmt_bind_param(
            $stmt, "si", $pencarian, $kategori_id
        );
    } elseif ($keyword !== "") {
        $pencarian = "%" . $keyword . "%";
        mysqli_stmt_bind_param($stmt, "s", $pencarian);
    } elseif ($kategori_id !== null) {
        mysqli_stmt_bind_param($stmt, "i", $kategori_id);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $data = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    mysqli_stmt_close($stmt);

    // 5. Tampilkan hasil JSON
    response_json(true, "Data produk berhasil diambil", [
        "jumlah" => count($data),
        "pencarian" => $keyword,
        "kategori_id" => $kategori_id,
        "produk" => $data
    ]);

} catch (Throwable $e) {
    response_json(false, "Terjadi kesalahan pada server", null, 500);
}
?>

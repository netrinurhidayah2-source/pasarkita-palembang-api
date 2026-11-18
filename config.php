
<?php
/**
 * CONFIGURATION PASAR KITA
 * Jangan menyimpan password database di GitHub.
 */

// Mencegah akses langsung ke file konfigurasi
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
    http_response_code(403);
    exit('Akses ditolak.');
}

// Konfigurasi database
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$db   = getenv('DB_NAME') ?: 'pasarkita';

// Koneksi database
$conn = mysqli_connect($host, $user, $pass, $db);

// Penanganan error tanpa membocorkan informasi database
if (!$conn) {
    error_log('Database connection failed: ' . mysqli_connect_error());
    http_response_code(500);
    exit('Terjadi kesalahan koneksi. Silakan coba beberapa saat lagi.');
}

// Charset database
mysqli_set_charset($conn, 'utf8mb4');

// Zona waktu
date_default_timezone_set('Asia/Jakarta');

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Format Rupiah
function rupiah($angka) {
    return "Rp " . number_format((float)$angka, 0, ',', '.');
}

// Gambar Produk
function gambar_produk($nama, $id) {
    $nama = strtolower(trim($nama));

    // Produk baru
    if (str_contains($nama, 'lempok')) return "/pasarkita/uploads/lempok%20durian.jpeg";
    if (str_contains($nama, 'tanjak')) return "/pasarkita/uploads/tanjak%20palembang.jpeg";
    if (str_contains($nama, 'malbi')) return "/pasarkita/uploads/malbi.jpeg";
    if (str_contains($nama, 'maksuba')) return "/pasarkita/uploads/kue%20maksuba.jpeg";
    if (str_contains($nama, 'delapan jam') || str_contains($nama, '8 jam')) return "/pasarkita/uploads/kue%20delapan%20jam.jpeg";

    // Produk lama
    if (str_contains($nama, 'kapal selam')) return "/pasarkita/uploads/pempek%20kapal%20selam.jpeg";
    if (str_contains($nama, 'lenjer')) return "/pasarkita/uploads/pempek%20lenjer.jpeg";
    if (str_contains($nama, 'adaan')) return "/pasarkita/uploads/pempek%20adaan.jpeg";
    if (str_contains($nama, 'kulit')) return "/pasarkita/uploads/pempek%20kulit.jpeg";
    if (str_contains($nama, 'pempek')) return "/pasarkita/uploads/pempek%20palembang.jpeg";
    if (str_contains($nama, 'tekwan')) return "/pasarkita/uploads/tekwan.jpeg";
    if (str_contains($nama, 'model')) return "/pasarkita/uploads/model%20ikan.jpeg";
    if (str_contains($nama, 'kemplang') || str_contains($nama, 'kerupuk')) return "/pasarkita/uploads/kemplang%20palembang.jpeg";
    if (str_contains($nama, 'songket')) return "/pasarkita/uploads/songket%20palembang.jpeg";
    if (str_contains($nama, 'jumputan') || str_contains($nama, 'kain')) return "/pasarkita/uploads/kain%20jumputan.jpeg";

    return "/pasarkita/uploads/pempek%20palembang.jpeg";
}
?>

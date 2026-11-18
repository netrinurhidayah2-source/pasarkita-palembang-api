<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "pasarkita";
$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) { die("Koneksi gagal: " . mysqli_connect_error()); }
date_default_timezone_set('Asia/Jakarta');
if (session_status() == PHP_SESSION_NONE) { session_start(); }
function rupiah($angka){ return "Rp " . number_format($angka, 0, ',', '.'); }

function gambar_produk($nama, $id){
    $nama = strtolower(trim($nama));

    // == PRODUK BARU - GAMBAR NYUSUL, NANTI TINGGAL TARUH FILE-NYA ==
    // Nama file yang harus kamu carikan / save di uploads/ :
    if(str_contains($nama,'lempok')) return "/pasarkita/uploads/lempok%20durian.jpeg"; // file: lempok durian.jpeg
    if(str_contains($nama,'tanjak')) return "/pasarkita/uploads/tanjak%20palembang.jpeg"; // file: tanjak palembang.jpeg
    if(str_contains($nama,'malbi')) return "/pasarkita/uploads/malbi.jpeg"; // file: malbi.jpeg
    if(str_contains($nama,'maksuba')) return "/pasarkita/uploads/kue%20maksuba.jpeg"; // file: kue maksuba.jpeg
    if(str_contains($nama,'delapan jam') || str_contains($nama,'8 jam')) return "/pasarkita/uploads/kue%20delapan%20jam.jpeg"; // file: kue delapan jam.jpeg

    // == PRODUK LAMA - SESUAI SCREENSHOT UPLOADS KAMU ==
    if(str_contains($nama,'kapal selam')) return "/pasarkita/uploads/pempek%20kapal%20selam.jpeg";
    if(str_contains($nama,'lenjer')) return "/pasarkita/uploads/pempek%20lenjer.jpeg";
    if(str_contains($nama,'adaan')) return "/pasarkita/uploads/pempek%20adaan.jpeg";
    if(str_contains($nama,'kulit')) return "/pasarkita/uploads/pempek%20kulit.jpeg";
    if(str_contains($nama,'pempek')) return "/pasarkita/uploads/pempek%20palembang.jpeg";
    if(str_contains($nama,'tekwan')) return "/pasarkita/uploads/tekwan.jpeg";
    if(str_contains($nama,'model')) return "/pasarkita/uploads/model%20ikan.jpeg";
    if(str_contains($nama,'kemplang') || str_contains($nama,'kerupuk')) return "/pasarkita/uploads/kemplang%20palembang.jpeg";
    if(str_contains($nama,'songket')) return "/pasarkita/uploads/songket%20palembang.jpeg";
    if(str_contains($nama,'jumputan') || str_contains($nama,'kain')) return "/pasarkita/uploads/kain%20jumputan.jpeg";

    return "/pasarkita/uploads/pempek%20palembang.jpeg";
}
?>
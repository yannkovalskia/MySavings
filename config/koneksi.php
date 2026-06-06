<?php
// 1. Pengaturan Database
$host     = "localhost";
$username = "root";
$password = "";
$database = "mysavings";

// 2. Membuat Koneksi menggunakan MySQLi
$koneksi = mysqli_connect($host, $username, $password, $database);

// 3. Cek Koneksi (Opsional, tapi wajib buat dokumentasi/debugging)
if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// 4. Mengaktifkan Session PHP
// Semua halaman yang membutuhkan login (Dashboard, Transaksi, dll) wajib meng-include file ini.
// Jadi session_start() ditaruh di sini agar kalian tidak perlu ketik manual di setiap file.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
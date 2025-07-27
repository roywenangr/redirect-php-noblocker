<?php
/**
 * index.php
 * Skrip ini akan berjalan di Heroku.
 * Tujuannya adalah mengambil daftar URL dari sebuah API eksternal,
 * memilih satu URL secara acak, dan mengalihkan pengunjung ke sana.
 */

// 1. Definisikan URL API Anda
// Ini adalah sumber data untuk daftar tujuan redirect.
$apiUrl = 'https://api.beon.my.id/api/domainlist/index.php';

/**
 * 2. Fungsi untuk melakukan redirect.
 *
 * @param string $url URL tujuan pengalihan.
 */
function performRedirect($url) {
    // Menggunakan redirect 302 (Found). Ini adalah status yang tepat untuk
    // pengalihan yang tujuannya bisa berubah-ubah atau bersifat sementara (seperti rotator).
    header("HTTP/1.1 302 Found");
    header("Location: " . $url);

    // Penting: Hentikan eksekusi skrip setelah mengirim header redirect
    // untuk memastikan tidak ada output lain yang dikirim.
    exit();
}

// 3. Blok utama untuk mengambil data dan menjalankan logika
try {
    // Mengambil konten JSON dari API.
    // Tanda '@' digunakan untuk menekan pesan warning jika URL tidak dapat diakses,
    // karena kita akan menangani error-nya secara manual di bawah.
    $jsonData = @file_get_contents($apiUrl);

    // Jika gagal mengambil data dari API (misal: API down, URL salah, atau tidak ada koneksi).
    if ($jsonData === false) {
        throw new Exception("Gagal terhubung atau mengambil data dari API di: " . htmlspecialchars($apiUrl));
    }

    // Mendekode string JSON menjadi array PHP.
    $urlList = json_decode($jsonData, true);

    // Memeriksa apakah proses decode JSON menghasilkan error.
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Data yang diterima dari API bukan format JSON yang valid.");
    }

    // 4. Memilih URL acak dan melakukan redirect
    // Pastikan hasil decode adalah array dan tidak kosong.
    if (is_array($urlList) && !empty($urlList)) {
        // Memilih key (indeks) acak dari array daftar URL.
        $randomKey = array_rand($urlList);

        // Mengambil nilai URL dari array menggunakan key acak tersebut.
        $targetUrl = $urlList[$randomKey];

        // Melakukan redirect ke URL yang terpilih.
        performRedirect($targetUrl);
    } else {
        // Jika API mengembalikan array kosong atau data tidak valid.
        echo "Daftar URL tujuan tidak tersedia atau kosong.";
    }

} catch (Exception $e) {
    // Menangkap dan menampilkan pesan error jika terjadi masalah selama proses.
    header("HTTP/1.1 500 Internal Server Error");
    echo "Terjadi kesalahan pada server redirect: " . $e->getMessage();
}

?>

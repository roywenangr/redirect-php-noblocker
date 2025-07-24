<?php
$data = file_exists("data.json") ? json_decode(file_get_contents("data.json"), true) : [];
$counterFile = 'counter.txt';
$counter = file_exists($counterFile) ? (int)file_get_contents($counterFile) : 0;

// Ambil link aktif
$activeLinks = array_values(array_filter($data, fn($d) => $d['active']));

// Jika tidak ada link aktif
if (count($activeLinks) === 0) {
    http_response_code(404);
    echo "Tidak ada link aktif.";
    exit;
}

// Ambil target & update counter
$target = $activeLinks[$counter % count($activeLinks)]['url'];
file_put_contents($counterFile, $counter + 1);

// Simpan jumlah klik
$clickLog = 'clicks.json';
$clicks = file_exists($clickLog) ? json_decode(file_get_contents($clickLog), true) : [];
$clicks[$target] = ($clicks[$target] ?? 0) + 1;
file_put_contents($clickLog, json_encode($clicks, JSON_PRETTY_PRINT));

// Redirect
header("Location: $target");
exit;
?>

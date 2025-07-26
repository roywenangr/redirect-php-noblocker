<?php
require_once 'init.php';

// Ambil link aktif dan urutan terakhir
$stmt = $db->prepare("SELECT * FROM links WHERE active = 1 ORDER BY id ASC");
$links = $stmt->execute() ? $stmt->fetchAll(SQLITE3_ASSOC) : [];

if (!$links) {
    die("No active links.");
}

// Ambil index rotasi terakhir dari session
session_start();
if (!isset($_SESSION['last_index'])) {
    $_SESSION['last_index'] = 0;
} else {
    $_SESSION['last_index'] = ($_SESSION['last_index'] + 1) % count($links);
}

$selected = $links[$_SESSION['last_index']];

// Tambah klik
$db->exec("UPDATE links SET clicks = clicks + 1 WHERE id = {$selected['id']}");

header("Location: " . $selected['url']);
exit;

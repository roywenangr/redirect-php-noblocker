<?php
$db = new PDO('sqlite:database.sqlite');

$db->exec("CREATE TABLE IF NOT EXISTS redirects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    url TEXT NOT NULL
)");
$db->exec("CREATE TABLE IF NOT EXISTS rolling_index (
    id INTEGER PRIMARY KEY,
    last_index INTEGER
)");

$expectedParam = 'special-offerxjnck';
$hasValidParam = isset($_GET[$expectedParam]);

if (!$hasValidParam) {
    header("Location: https://yahoo.co.jp");
    exit;
}

$links = $db->query("SELECT url FROM redirects")->fetchAll(PDO::FETCH_COLUMN);
if (count($links) === 0) {
    echo "Belum ada link redirect ditambahkan.";
    exit;
}

$lastIndex = $db->query("SELECT last_index FROM rolling_index WHERE id = 1")->fetchColumn();
if ($lastIndex === false) $lastIndex = -1;

$nextIndex = ($lastIndex + 1) % count($links);

$stmt = $db->prepare("REPLACE INTO rolling_index (id, last_index) VALUES (1, :next)");
$stmt->execute(['next' => $nextIndex]);

header("Location: " . $links[$nextIndex]);
exit;

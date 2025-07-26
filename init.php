<?php
$db = new SQLite3(__DIR__ . '/data.db');

$db->exec("CREATE TABLE IF NOT EXISTS links (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    url TEXT NOT NULL,
    active INTEGER DEFAULT 1,
    clicks INTEGER DEFAULT 0
)");

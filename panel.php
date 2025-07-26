<?php
session_start();
$password = 'admin123';

$db = new PDO('sqlite:database.sqlite');
$db->exec("CREATE TABLE IF NOT EXISTS redirects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    url TEXT NOT NULL
)");

if (isset($_POST['password'])) {
    if ($_POST['password'] === $password) {
        $_SESSION['logged_in'] = true;
    }
}
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: panel.php");
    exit;
}
if (!isset($_SESSION['logged_in'])):
?>
<form method="POST">
    <h2>Login Panel</h2>
    <input type="password" name="password" placeholder="Password">
    <button type="submit">Login</button>
</form>
<?php exit; endif;

if (isset($_POST['url'])) {
    $url = trim($_POST['url']);
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        $stmt = $db->prepare("INSERT INTO redirects (url) VALUES (:url)");
        $stmt->execute(['url' => $url]);
        $msg = "Link berhasil ditambahkan.";
    } else {
        $msg = "URL tidak valid.";
    }
}

$rows = $db->query("SELECT * FROM redirects")->fetchAll(PDO::FETCH_ASSOC);
?>
<h2>Panel Redirect</h2>
<a href="?logout=1">Logout</a>
<?php if (isset($msg)) echo "<p>$msg</p>"; ?>
<form method="POST">
    <input type="text" name="url" placeholder="https://example.com" required>
    <button type="submit">Tambah Redirect</button>
</form>

<h3>Daftar Redirect:</h3>
<ol>
<?php foreach ($rows as $row): ?>
    <li><?= htmlspecialchars($row['url']) ?></li>
<?php endforeach; ?>
</ol>

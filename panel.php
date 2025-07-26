<?php
require_once 'init.php';
session_start();
$password = "admin123";

if (isset($_POST['password']) && $_POST['password'] === $password) {
    $_SESSION['logged_in'] = true;
}

if (!isset($_SESSION['logged_in'])):
?>
<!DOCTYPE html>
<html><body><form method="POST"><input type="password" name="password"/><button>Login</button></form></body></html>
<?php exit; endif;

// Tambah link
if (isset($_POST['new_link'])) {
    $url = $_POST['new_link'];
    $stmt = $db->prepare("INSERT INTO links (url, active, clicks) VALUES (:url, 1, 0)");
    $stmt->bindValue(":url", $url, SQLITE3_TEXT);
    $stmt->execute();
}

// Hapus link
if (isset($_POST['delete'])) {
    $db->exec("DELETE FROM links WHERE id = " . (int)$_POST['delete']);
}

// Toggle aktif
if (isset($_POST['toggle'])) {
    $id = (int)$_POST['toggle'];
    $db->exec("UPDATE links SET active = NOT active WHERE id = $id");
}

// Ambil data
$res = $db->query("SELECT * FROM links ORDER BY id ASC");
$links = [];
while ($row = $res->fetchArray(SQLITE3_ASSOC)) $links[] = $row;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Redirect Panel</title>
    <style>
        body { font-family: sans-serif; background: #121212; color: #eee; padding: 2em; }
        table { width: 100%; border-collapse: collapse; margin-top: 1em; }
        td, th { padding: .5em; border: 1px solid #444; text-align: center; }
        form { display: inline; }
        input[type="text"] { width: 300px; padding: 0.5em; background: #222; color: #fff; border: 1px solid #444; }
        button { background: #333; color: #fff; padding: 0.4em 1em; border: none; cursor: pointer; }
        button:hover { background: #555; }
    </style>
</head>
<body>
    <h2>Redirect Panel</h2>
    <form method="POST">
        <input type="text" name="new_link" placeholder="https://example.com" required>
        <button type="submit">Tambah Link</button>
    </form>
    <table>
        <tr><th>ID</th><th>URL</th><th>Aktif</th><th>Clicks</th><th>Aksi</th></tr>
        <?php foreach ($links as $link): ?>
            <tr>
                <td><?= $link['id'] ?></td>
                <td><?= htmlspecialchars($link['url']) ?></td>
                <td><?= $link['active'] ? '✅' : '❌' ?></td>
                <td><?= $link['clicks'] ?></td>
                <td>
                    <form method="POST"><input type="hidden" name="toggle" value="<?= $link['id'] ?>"><button>Toggle</button></form>
                    <form method="POST"><input type="hidden" name="delete" value="<?= $link['id'] ?>"><button>Hapus</button></form>
                </td>
            </tr>
        <?php endforeach ?>
    </table>
</body>
</html>

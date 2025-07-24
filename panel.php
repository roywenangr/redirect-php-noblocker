<?php
session_start();
$panelPassword = "admin123";

if (isset($_POST['password'])) {
    $_SESSION['logged_in'] = ($_POST['password'] === $panelPassword);
    if (!$_SESSION['logged_in']) $error = "Password salah!";
}
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: panel.php");
    exit;
}

$data = file_exists("data.json") ? json_decode(file_get_contents("data.json"), true) : [];
$counterFile = 'counter.txt';
$counter = file_exists($counterFile) ? (int)file_get_contents($counterFile) : 0;
$clicks = file_exists("clicks.json") ? json_decode(file_get_contents("clicks.json"), true) : [];

if (isset($_POST['save']) && $_SESSION['logged_in']) {
    $newData = [];
    foreach ($_POST['url'] as $i => $url) {
        $url = trim($url);
        $active = isset($_POST['active'][$i]);
        $delete = isset($_POST['delete']) && in_array($i, $_POST['delete']);
        if (!$delete && $url !== "" && filter_var($url, FILTER_VALIDATE_URL)) {
            $newData[] = ["url" => $url, "active" => $active];
        }
    }
    $activeLinks = array_filter($newData, fn($d) => $d['active']);
    if (count($activeLinks) > 0 && $counter >= count($activeLinks)) {
        $counter = $counter % count($activeLinks);
        file_put_contents($counterFile, $counter);
    }
    file_put_contents("data.json", json_encode($newData, JSON_PRETTY_PRINT));
    $data = $newData;
    $success = "Data berhasil disimpan!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Panel Redirect</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--text);
            margin: 0;
            padding: 2rem;
            transition: background 0.3s, color 0.3s;
        }
        :root {
            --bg: #f6f7fb;
            --text: #333;
            --form-bg: #fff;
            --border: #ddd;
            --hover: #f9f9f9;
            --input-bg: #fff;
            --input-border: #ccc;
            --btn-bg: #007bff;
            --btn-hover: #0056b3;
            --logout-bg: #dc3545;
            --logout-hover: #c82333;
        }
        body.dark {
            --bg: #121212;
            --text: #e0e0e0;
            --form-bg: #1e1e1e;
            --border: #333;
            --hover: #2c2c2c;
            --input-bg: #2a2a2a;
            --input-border: #444;
            --btn-bg: #0d6efd;
            --btn-hover: #0b5ed7;
            --logout-bg: #c82333;
            --logout-hover: #a71d2a;
        }
        h2 { text-align: center; margin-bottom: 1rem; }
        form {
            max-width: 800px;
            margin: auto;
            background: var(--form-bg);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; }
        th, td {
            padding: 0.6rem 0.8rem;
            border-bottom: 1px solid var(--border);
            text-align: left;
        }
        tr:hover td { background: var(--hover); }
        input[type="text"], input[type="url"] {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid var(--input-border);
            border-radius: 6px;
            background-color: var(--input-bg);
            color: var(--text);
        }
        input[type="checkbox"] { transform: scale(1.2); }
        button {
            background: var(--btn-bg);
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            font-size: 1rem;
            border-radius: 8px;
            cursor: pointer;
        }
        button:hover { background: var(--btn-hover); }
        .success { color: green; margin-top: 1rem; }
        .logout {
            float: right;
            text-decoration: none;
            background: var(--logout-bg);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
        }
        .logout:hover { background: var(--logout-hover); }
        .dark-toggle {
            float: left;
            cursor: pointer;
            font-size: 0.9rem;
            background: transparent;
            border: none;
            color: var(--text);
            text-decoration: underline;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
<?php if (!isset($_SESSION['logged_in'])): ?>
<h2>Login Panel</h2>
<form method="POST">
    <input type="password" name="password" placeholder="Password">
    <button type="submit">Login</button>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
</form>
<?php else: ?>
<h2>Redirect Link Manager</h2>
<form method="POST">
    <button type="button" class="dark-toggle" onclick="toggleDark()">Toggle Dark Mode</button>
    <a class="logout" href="?logout=1">Logout</a>
    <table border="1">
        <tr><th>#</th><th>URL</th><th>Aktif?</th><th>Click</th><th>Hapus?</th></tr>
        <?php foreach ($data as $i => $entry): $url = $entry['url']; ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><input name="url[]" value="<?= htmlspecialchars($url) ?>"></td>
            <td><input type="checkbox" name="active[<?= $i ?>]" <?= $entry['active'] ? 'checked' : '' ?>></td>
            <td><?= $clicks[$url] ?? 0 ?></td>
            <td><input type="checkbox" name="delete[]" value="<?= $i ?>"></td>
        </tr>
        <?php endforeach; ?>
        <tr>
            <td>+</td>
            <td><input name="url[]" placeholder="https://example.com"></td>
            <td><input type="checkbox" name="active[<?= count($data) ?>]" checked></td>
            <td>0</td>
            <td></td>
        </tr>
    </table>
    <button type="submit" name="save">Simpan Perubahan</button>
    <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
</form>
<script>
    function toggleDark() {
        document.body.classList.toggle('dark');
        localStorage.setItem('theme', document.body.classList.contains('dark') ? 'dark' : 'light');
    }
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark');
    }
</script>
<?php endif; ?>
</body>
</html>

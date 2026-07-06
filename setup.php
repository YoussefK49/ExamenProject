<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$dbName = 'instant_app';
$mysqli = new mysqli($host, $user, $pass);
if ($mysqli->connect_error) {
    die('Database connect error: ' . $mysqli->connect_error);
}

if (!$mysqli->query("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
    die('Database creation failed: ' . $mysqli->error);
}

$mysqli->select_db($dbName);
$sql = file_get_contents(__DIR__ . '/database.sql');

if (!$mysqli->multi_query($sql)) {
    die('Schema import failed: ' . $mysqli->error);
}

while ($mysqli->more_results() && $mysqli->next_result()) {
    // flush multi query results
}

$result = $mysqli->query("SHOW COLUMNS FROM users LIKE 'bio'");
if ($result && $result->num_rows === 0) {
    $mysqli->query("ALTER TABLE users ADD COLUMN bio TEXT DEFAULT NULL AFTER avatar");
}

?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instant Setup</title>
    <style>body{font-family:sans-serif;background:#f6f7fb;color:#1f2330;padding:2rem;} .card{background:#fff;padding:2rem;border-radius:16px;max-width:560px;margin:0 auto;box-shadow:0 10px 30px rgba(0,0,0,.08);}</style>
</head>
<body>
    <div class="card">
        <h1>Instant database klaar</h1>
        <p>De database <strong><?php echo htmlspecialchars($dbName, ENT_QUOTES, 'UTF-8'); ?></strong> is aangemaakt en de tabellen zijn ingesteld.</p>
        <p>Open <a href="index.php">index.php</a> om de app te bekijken.</p>
    </div>
</body>
</html>

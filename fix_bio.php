<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$dbName = 'instant_app';

$mysqli = new mysqli($host, $user, $pass, $dbName);
if ($mysqli->connect_error) {
    die('Database connect error: ' . $mysqli->connect_error);
}

echo "Verbonden met database: $dbName<br>";

// Check if bio column exists
$result = $mysqli->query("SHOW COLUMNS FROM users LIKE 'bio'");
echo "Aantal bio kolommen gevonden: " . $result->num_rows . "<br>";

if ($result->num_rows == 0) {
    // Add bio column
    $alterResult = $mysqli->query("ALTER TABLE users ADD COLUMN bio TEXT DEFAULT NULL");
    if ($alterResult) {
        echo "✓ Bio kolom toegevoegd aan users tabel.<br>";
    } else {
        echo "✗ Fout bij toevoegen bio kolom: " . $mysqli->error . "<br>";
    }
} else {
    echo "✓ Bio kolom bestaat al.<br>";
}

// Verify
$verifyResult = $mysqli->query("SHOW COLUMNS FROM users LIKE 'bio'");
echo "Na controle - Aantal bio kolommen: " . $verifyResult->num_rows . "<br>";

echo "<br><a href='index.php' style='color: #0095f6; text-decoration: none; font-weight: 600;'>Ga naar index.php</a>";
?>

<?php
/**
 * Authenticatie-endpoint.
 *
 * Criterium "Hash & Salt": wachtwoorden worden NOOIT in platte tekst opgeslagen.
 * password_hash() gebruikt bcrypt en genereert automatisch een unieke salt per
 * wachtwoord, verwerkt in de hash-string zelf.
 *
 * Criterium "Voorkomen SQL Injecties": alle queries gebruiken PDO prepared
 * statements met "?" placeholders — user-input wordt nooit direct in de
 * SQL-string geplakt.
 */

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

session_start();

$action = $_GET['action'] ?? '';
$input  = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    $pdo = getPDO();

    if ($action === 'register') {
        $username = trim($input['username'] ?? '');
        $email    = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';

        if (!$username || !$email || strlen($password) < 8) {
            http_response_code(422);
            echo json_encode(['error' => 'Ongeldige invoer. Wachtwoord moet minimaal 8 tekens zijn.']);
            exit;
        }

        // Hash + salt (bcrypt, cost 12)
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        $stmt = $pdo->prepare(
            'INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)'
        );
        $stmt->execute([$username, $email, $hash]);

        $_SESSION['user_id'] = (int) $pdo->lastInsertId();

        echo json_encode(['success' => true, 'user_id' => $_SESSION['user_id'], 'username' => $username]);
        exit;
    }

    if ($action === 'login') {
        $email    = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';

        $stmt = $pdo->prepare('SELECT id, username, password_hash FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // password_verify vergelijkt tegen de opgeslagen hash (salt zit erin verwerkt)
        if (!$user || !password_verify($password, $user['password_hash'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Onjuiste inloggegevens.']);
            exit;
        }

        $_SESSION['user_id'] = $user['id'];

        echo json_encode(['success' => true, 'username' => $user['username']]);
        exit;
    }

    http_response_code(404);
    echo json_encode(['error' => 'Onbekende actie.']);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Serverfout: ' . $e->getMessage()]);
}

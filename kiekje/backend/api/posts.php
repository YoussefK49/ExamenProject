<?php
/**
 * Posts-endpoint: ophalen van de feed en nieuwe kiekjes plaatsen.
 * Alle input gaat via prepared statements (voorkomt SQL-injectie).
 */

require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

session_start();
$pdo = getPDO();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Feed ophalen, nieuwste eerst, met like-aantal
    $stmt = $pdo->query(
        'SELECT p.id, p.image_url, p.caption, p.location, p.created_at,
                u.username, u.avatar_url,
                (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count
         FROM posts p
         JOIN users u ON u.id = p.user_id
         ORDER BY p.created_at DESC
         LIMIT 50'
    );
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($method === 'POST') {
    if (empty($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Niet ingelogd.']);
        exit;
    }

    $input   = json_decode(file_get_contents('php://input'), true) ?? [];
    $image   = $input['image_url'] ?? '';
    $caption = $input['caption'] ?? '';
    $loc     = $input['location'] ?? null;

    if (!$image) {
        http_response_code(422);
        echo json_encode(['error' => 'Afbeelding is verplicht.']);
        exit;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO posts (user_id, image_url, caption, location) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$_SESSION['user_id'], $image, $caption, $loc]);

    echo json_encode(['success' => true, 'post_id' => $pdo->lastInsertId()]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Methode niet toegestaan.']);

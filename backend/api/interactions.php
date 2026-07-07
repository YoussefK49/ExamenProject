<?php
/**
 * Likes & comments-endpoint.
 * ?action=like        (POST, body: {post_id})   -> toggelt like
 * ?action=comment     (POST, body: {post_id, content})
 * ?action=comments    (GET,  query: post_id)     -> lijst reacties
 */

require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');
session_start();
$pdo = getPDO();

$action = $_GET['action'] ?? '';

if (empty($_SESSION['user_id']) && $action !== 'comments') {
    http_response_code(401);
    echo json_encode(['error' => 'Niet ingelogd.']);
    exit;
}

if ($action === 'like') {
    $input   = json_decode(file_get_contents('php://input'), true) ?? [];
    $postId  = (int) ($input['post_id'] ?? 0);
    $userId  = $_SESSION['user_id'];

    $check = $pdo->prepare('SELECT id FROM likes WHERE post_id = ? AND user_id = ?');
    $check->execute([$postId, $userId]);

    if ($check->fetch()) {
        $del = $pdo->prepare('DELETE FROM likes WHERE post_id = ? AND user_id = ?');
        $del->execute([$postId, $userId]);
        echo json_encode(['liked' => false]);
    } else {
        $ins = $pdo->prepare('INSERT INTO likes (post_id, user_id) VALUES (?, ?)');
        $ins->execute([$postId, $userId]);
        echo json_encode(['liked' => true]);
    }
    exit;
}

if ($action === 'comment') {
    $input   = json_decode(file_get_contents('php://input'), true) ?? [];
    $postId  = (int) ($input['post_id'] ?? 0);
    $content = trim($input['content'] ?? '');

    if (!$content) {
        http_response_code(422);
        echo json_encode(['error' => 'Lege reactie.']);
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)');
    $stmt->execute([$postId, $_SESSION['user_id'], $content]);
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'comments') {
    $postId = (int) ($_GET['post_id'] ?? 0);
    $stmt = $pdo->prepare(
        'SELECT c.content, c.created_at, u.username
         FROM comments c JOIN users u ON u.id = c.user_id
         WHERE c.post_id = ? ORDER BY c.created_at ASC'
    );
    $stmt->execute([$postId]);
    echo json_encode($stmt->fetchAll());
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Onbekende actie.']);

<?php
require_once 'db.php';
require_once 'auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

requireLogin();

$action = $_POST['action'] ?? '';
$userId = getCurrentUserId();

if (($action === 'like' || $action === 'unlike') && !empty($_POST['post_id'])) {
    $postId = (int) $_POST['post_id'];
    if ($action === 'unlike') {
        removeLike($postId, $userId);
        $isLiked = false;
    } else {
        $isLiked = toggleLike($postId, $userId);
    }
    $likeCount = getPostLikeCount($postId);
    echo json_encode(['success' => true, 'isLiked' => $isLiked, 'likeCount' => $likeCount]);
    exit;
}

if ($action === 'comment' && !empty($_POST['post_id']) && !empty(trim($_POST['comment_text']))) {
    $postId = (int) $_POST['post_id'];
    $commentText = trim($_POST['comment_text']);
    addComment($postId, $userId, $commentText);
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
?>

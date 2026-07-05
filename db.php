<?php
$DB_HOST = '127.0.0.1';
$DB_NAME = 'instant_app';
$DB_USER = 'root';
$DB_PASS = '';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_error) {
    die('Database connect error: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

function getPosts($limit = 10) {
    global $mysqli;
    $sql = "SELECT p.id, p.caption, p.image_url, p.created_at, u.username, u.avatar,
        COALESCE(l.likes, 0) AS like_count,
        COALESCE(c.comments, 0) AS comment_count
        FROM posts p
        JOIN users u ON p.user_id = u.id
        LEFT JOIN (
            SELECT post_id, COUNT(*) AS likes
            FROM likes
            GROUP BY post_id
        ) l ON l.post_id = p.id
        LEFT JOIN (
            SELECT post_id, COUNT(*) AS comments
            FROM comments
            GROUP BY post_id
        ) c ON c.post_id = p.id
        ORDER BY p.created_at DESC
        LIMIT ?";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $posts = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $posts;
}

function getPostsByUser($userId, $limit = 10) {
    global $mysqli;
    $sql = "SELECT p.id, p.caption, p.image_url, p.created_at, u.username, u.avatar,
        COALESCE(l.likes, 0) AS like_count,
        COALESCE(c.comments, 0) AS comment_count
        FROM posts p
        JOIN users u ON p.user_id = u.id
        LEFT JOIN (
            SELECT post_id, COUNT(*) AS likes
            FROM likes
            GROUP BY post_id
        ) l ON l.post_id = p.id
        LEFT JOIN (
            SELECT post_id, COUNT(*) AS comments
            FROM comments
            GROUP BY post_id
        ) c ON c.post_id = p.id
        WHERE p.user_id = ?
        ORDER BY p.created_at DESC
        LIMIT ?";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ii', $userId, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $posts = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $posts;
}

function addLike($postId, $userId) {
    global $mysqli;
    $stmt = $mysqli->prepare("INSERT IGNORE INTO likes (post_id, user_id, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param('ii', $postId, $userId);
    $stmt->execute();
    $stmt->close();
}

function removeLike($postId, $userId) {
    global $mysqli;
    $stmt = $mysqli->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?");
    $stmt->bind_param('ii', $postId, $userId);
    $stmt->execute();
    $stmt->close();
}

function getPostLikeCount($postId) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT COUNT(*) AS count FROM likes WHERE post_id = ?");
    $stmt->bind_param('i', $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return (int) ($row['count'] ?? 0);
}

function toggleLike($postId, $userId) {
    if (isLiked($postId, $userId)) {
        removeLike($postId, $userId);
        return false;
    } else {
        addLike($postId, $userId);
        return true;
    }
}

function addComment($postId, $userId, $message) {
    global $mysqli;
    $stmt = $mysqli->prepare("INSERT INTO comments (post_id, user_id, comment_text, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param('iis', $postId, $userId, $message);
    $stmt->execute();
    $stmt->close();
}

function getUser($id) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT id, username, avatar FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user;
}

function getStories($limit = 10) {
    global $mysqli;
    $sql = "SELECT s.id, s.image_url, s.created_at, s.expires_at, u.username, u.avatar
            FROM stories s
            JOIN users u ON s.user_id = u.id
            WHERE s.expires_at > NOW()
            ORDER BY s.created_at DESC
            LIMIT ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $stories = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $stories;
}

function addStory($userId, $imageUrl = null) {
    global $mysqli;
    $stmt = $mysqli->prepare("INSERT INTO stories (user_id, image_url) VALUES (?, ?)");
    $stmt->bind_param('is', $userId, $imageUrl);
    $stmt->execute();
    $stmt->close();
}

function getStoriesByUser($userId, $limit = 10) {
    global $mysqli;
    $sql = "SELECT s.id, s.image_url, s.created_at, s.expires_at, u.username, u.avatar
            FROM stories s
            JOIN users u ON s.user_id = u.id
            WHERE s.user_id = ? AND s.expires_at > NOW()
            ORDER BY s.created_at DESC
            LIMIT ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ii', $userId, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $stories = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $stories;
}

function createPost($userId, $caption, $imageUrl = null) {
    global $mysqli;
    $stmt = $mysqli->prepare("INSERT INTO posts (user_id, caption, image_url) VALUES (?, ?, ?)");
    $stmt->bind_param('iss', $userId, $caption, $imageUrl);
    $stmt->execute();
    $stmt->close();
}

function registerUser($username, $email, $password) {
    global $mysqli;
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $username, $email, $passwordHash);
    $result = $stmt->execute();
    $userId = $stmt->insert_id;
    $stmt->close();
    return $result ? $userId : false;
}

function loginUser($username, $password) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT id, username, email, password_hash, avatar FROM users WHERE username = ? OR email = ? LIMIT 1");
    $stmt->bind_param('ss', $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        unset($user['password_hash']);
        return $user;
    }
    return false;
}

function followUser($followerId, $followingId) {
    global $mysqli;
    $stmt = $mysqli->prepare("INSERT IGNORE INTO follows (follower_id, following_id) VALUES (?, ?)");
    $stmt->bind_param('ii', $followerId, $followingId);
    $stmt->execute();
    $stmt->close();
}

function unfollowUser($followerId, $followingId) {
    global $mysqli;
    $stmt = $mysqli->prepare("DELETE FROM follows WHERE follower_id = ? AND following_id = ?");
    $stmt->bind_param('ii', $followerId, $followingId);
    $stmt->execute();
    $stmt->close();
}

function isFollowing($followerId, $followingId) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT id FROM follows WHERE follower_id = ? AND following_id = ? LIMIT 1");
    $stmt->bind_param('ii', $followerId, $followingId);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();
    return $exists;
}

function searchUsers($query, $limit = 20) {
    global $mysqli;
    $query = trim($query);
    if ($query === '') {
        return [];
    }

    $like = '%' . $query . '%';
    $sql = "SELECT u.id, u.username, u.email, u.avatar, u.created_at,
            (SELECT COUNT(*) FROM posts p WHERE p.user_id = u.id) AS post_count
            FROM users u
            WHERE u.username LIKE ? OR u.email LIKE ?
            ORDER BY u.username ASC
            LIMIT ?";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ssi', $like, $like, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $users;
}

function getLikedPosts($userId, $limit = 20) {
    global $mysqli;
    $sql = "SELECT p.id, p.caption, p.image_url, p.created_at, u.username, u.avatar,
        COALESCE(l.likes, 0) AS like_count,
        COALESCE(c.comments, 0) AS comment_count,
        l2.created_at AS liked_at
        FROM likes l2
        JOIN posts p ON l2.post_id = p.id
        JOIN users u ON p.user_id = u.id
        LEFT JOIN (
            SELECT post_id, COUNT(*) AS likes
            FROM likes
            GROUP BY post_id
        ) l ON l.post_id = p.id
        LEFT JOIN (
            SELECT post_id, COUNT(*) AS comments
            FROM comments
            GROUP BY post_id
        ) c ON c.post_id = p.id
        WHERE l2.user_id = ?
        ORDER BY l2.created_at DESC
        LIMIT ?";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ii', $userId, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $posts = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $posts;
}

function isLiked($postId, $userId) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ? LIMIT 1");
    $stmt->bind_param('ii', $postId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();
    return $exists;
}

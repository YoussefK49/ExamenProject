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

function addLike($postId, $userId) {
    global $mysqli;
    $stmt = $mysqli->prepare("INSERT IGNORE INTO likes (post_id, user_id, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param('ii', $postId, $userId);
    $stmt->execute();
    $stmt->close();
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

function createPost($userId, $caption, $imageUrl = null) {
    global $mysqli;
    $stmt = $mysqli->prepare("INSERT INTO posts (user_id, caption, image_url) VALUES (?, ?, ?)");
    $stmt->bind_param('iss', $userId, $caption, $imageUrl);
    $stmt->execute();
    $stmt->close();
}

<<<<<<< HEAD
=======
<<<<<<< HEAD
function loginUser($username, $password) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT id, username, password_hash FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param('s', $username);
=======
>>>>>>> origin/main
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
<<<<<<< HEAD
=======
>>>>>>> origin/main
>>>>>>> origin/main
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if ($user && password_verify($password, $user['password_hash'])) {
<<<<<<< HEAD
        unset($user['password_hash']);
=======
<<<<<<< HEAD
=======
        unset($user['password_hash']);
>>>>>>> origin/main
>>>>>>> origin/main
        return $user;
    }
    return false;
}

<<<<<<< HEAD
=======
<<<<<<< HEAD
function registerUser($username, $email, $password) {
    global $mysqli;
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $mysqli->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $username, $email, $passwordHash);
    
    try {
        $stmt->execute();
        $stmt->close();
        return true;
    } catch (Exception $e) {
        $stmt->close();
        return false;
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
=======
>>>>>>> origin/main
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
<<<<<<< HEAD
=======
>>>>>>> origin/main
>>>>>>> origin/main
}

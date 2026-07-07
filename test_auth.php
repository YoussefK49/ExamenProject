<?php
require_once 'backend/config/database.php';

session_start();

// Test register
$username = 'testuser';
$email = 'test@example.com';
$password = 'test12345';

try {
    $pdo = getPDO();
    
    // Check if user already exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? OR username = ?');
    $stmt->execute([$email, $username]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        echo "User already exists, deleting for test...\n";
        $stmt = $pdo->prepare('DELETE FROM users WHERE email = ? OR username = ?');
        $stmt->execute([$email, $username]);
    }
    
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    
    $stmt = $pdo->prepare(
        'INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)'
    );
    $stmt->execute([$username, $email, $hash]);
    
    echo "Registration successful! User ID: " . $pdo->lastInsertId() . "\n";
    
    // Test login
    $stmt = $pdo->prepare('SELECT id, username, password_hash FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        echo "Login successful! Username: " . $user['username'] . "\n";
    } else {
        echo "Login failed!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

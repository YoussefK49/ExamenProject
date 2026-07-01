<?php
if (!is_dir(session_save_path())) {
    session_save_path(sys_get_temp_dir());
}
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    require_once 'db.php';
    return getUser(getCurrentUserId());
}

function logout() {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

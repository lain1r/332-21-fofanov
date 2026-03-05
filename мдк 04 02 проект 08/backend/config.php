<?php
// backend/config.php

// Secure session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Only 0 because we will run on localhost
session_start();

// Database initialization
function initDatabase()
{
    $db = getDB();
    $query = "CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        phone TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        secret_question TEXT NOT NULL,
        secret_answer TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    $db->exec($query);
}

// Get Database Connection
function getDB()
{
    $dbFile = __DIR__ . '/database.db';
    try {
        $db = new PDO('sqlite:' . $dbFile);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    }
    catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Redirect utility
function redirect($url)
{
    header("Location: $url");
    exit();
}

// Check if user is authenticated
function checkAuth()
{
    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }
}

// Validate phone format (+7-XXX-XXX-XX-XX)
function validatePhone($phone)
{
    return preg_match('/^\+7-\d{3}-\d{3}-\d{2}-\d{2}$/', $phone);
}

// Validate password (letters/digits/symbols, 6-10 characters)
function validatePassword($password)
{
    // only english letters, digits, symbols. length 6-10
    return preg_match('/^[a-zA-Z0-9!@#$%^&*()_+{}\[\]:;<>,.?~\\/-]{6,10}$/', $password);
}

// CSRF token generation
function getCsrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF token validation
function validateCsrfToken($token)
{
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    return true;
}

// Initialize database dynamically
initDatabase();
?>

<?php
require_once 'auth.php';
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$full_name = trim($_POST['full_name'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if ($username === '' || $password === '' || $confirm_password === '') {
    $_SESSION['flash_error'] = 'Username and password fields are required.';
    header('Location: index.php#admins');
    exit;
}

if ($password !== $confirm_password) {
    $_SESSION['flash_error'] = 'Passwords do not match.';
    header('Location: index.php#admins');
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
$stmt->execute([$username]);

if ($stmt->fetch()) {
    $_SESSION['flash_error'] = 'That username is already taken.';
    header('Location: index.php#admins');
    exit;
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

$insert = $pdo->prepare("
    INSERT INTO admins (full_name, username, password)
    VALUES (?, ?, ?)
");
$insert->execute([$full_name, $username, $hashed]);

$_SESSION['flash_success'] = 'Admin added successfully.';
header('Location: index.php#admins');
exit;
?>
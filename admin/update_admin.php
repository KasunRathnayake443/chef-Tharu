<?php
require_once 'auth.php';
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$full_name = trim($_POST['full_name'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if ($id <= 0 || $username === '') {
    $_SESSION['flash_error'] = 'Valid admin data is required.';
    header('Location: index.php#admins');
    exit;
}

$check = $pdo->prepare("SELECT id FROM admins WHERE username = ? AND id != ?");
$check->execute([$username, $id]);

if ($check->fetch()) {
    $_SESSION['flash_error'] = 'That username is already taken.';
    header('Location: index.php#admins');
    exit;
}

if ($password !== '' || $confirm_password !== '') {
    if ($password !== $confirm_password) {
        $_SESSION['flash_error'] = 'Passwords do not match.';
        header('Location: index.php#admins');
        exit;
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        UPDATE admins
        SET full_name = ?, username = ?, password = ?
        WHERE id = ?
    ");
    $stmt->execute([$full_name, $username, $hashed, $id]);
} else {
    $stmt = $pdo->prepare("
        UPDATE admins
        SET full_name = ?, username = ?
        WHERE id = ?
    ");
    $stmt->execute([$full_name, $username, $id]);
}

if ((int)$_SESSION['admin_id'] === $id) {
    $_SESSION['admin_username'] = $username;
}

$_SESSION['flash_success'] = 'Admin updated successfully.';
header('Location: index.php#admins');
exit;
?>
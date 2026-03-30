<?php
require_once 'auth.php';
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['flash_error'] = 'Invalid admin selected.';
    header('Location: index.php#admins');
    exit;
}

if ((int)$id === (int)$_SESSION['admin_id']) {
    $_SESSION['flash_error'] = 'You cannot delete the currently logged-in admin.';
    header('Location: index.php#admins');
    exit;
}

$count = (int)$pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();

if ($count <= 1) {
    $_SESSION['flash_error'] = 'At least one admin account must remain.';
    header('Location: index.php#admins');
    exit;
}

$stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
$stmt->execute([$id]);

$_SESSION['flash_success'] = 'Admin deleted successfully.';
header('Location: index.php#admins');
exit;
?>


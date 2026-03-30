<?php
require_once 'auth.php';
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['flash_error'] = 'Invalid category selected.';
    header('Location: index.php#categories');
    exit;
}

$stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
$stmt->execute([$id]);

$_SESSION['flash_success'] = 'Category deleted successfully.';
header('Location: index.php#categories');
exit;
?>
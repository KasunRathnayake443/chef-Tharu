<?php
require_once 'auth.php';
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$sort_order = (int)($_POST['sort_order'] ?? 0);

if ($id <= 0 || $name === '') {
    $_SESSION['flash_error'] = 'Valid category data is required.';
    header('Location: index.php#categories');
    exit;
}

$stmt = $pdo->prepare("UPDATE categories SET name = ?, sort_order = ? WHERE id = ?");
$stmt->execute([$name, $sort_order, $id]);

$_SESSION['flash_success'] = 'Category updated successfully.';
header('Location: index.php#categories');
exit;
?>
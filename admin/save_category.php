<?php
require_once 'auth.php';
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$name = trim($_POST['name'] ?? '');
$sort_order = (int)($_POST['sort_order'] ?? 0);

if ($name === '') {
    $_SESSION['flash_error'] = 'Category name is required.';
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("INSERT INTO categories (name, sort_order) VALUES (?, ?)");
$stmt->execute([$name, $sort_order]);

$_SESSION['flash_success'] = 'Category added successfully.';
header('Location: index.php#categories');
exit;
?>
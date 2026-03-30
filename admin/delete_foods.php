<?php
require_once 'auth.php';
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['flash_error'] = 'Invalid food selected.';
    header('Location: index.php#foods');
    exit;
}

$stmt = $pdo->prepare("SELECT image FROM foods WHERE id = ?");
$stmt->execute([$id]);
$food = $stmt->fetch();

if (!$food) {
    $_SESSION['flash_error'] = 'Food not found.';
    header('Location: index.php#foods');
    exit;
}

$deleteStmt = $pdo->prepare("DELETE FROM foods WHERE id = ?");
$deleteStmt->execute([$id]);

if (!empty($food['image'])) {
    $filePath = dirname(__DIR__) . '/uploads/foods/' . $food['image'];
    if (is_file($filePath)) {
        @unlink($filePath);
    }
}

$_SESSION['flash_success'] = 'Food deleted successfully.';
header('Location: index.php#foods');
exit;
?>
<?php
require_once 'auth.php';
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$status = trim($_POST['status'] ?? '');

$allowedStatuses = ['pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled'];

if ($id <= 0 || !in_array($status, $allowedStatuses, true)) {
    $_SESSION['flash_error'] = 'Invalid order status data.';
    header('Location: index.php#orders');
    exit;
}

$stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
$stmt->execute([$status, $id]);

$_SESSION['flash_success'] = 'Order status updated successfully.';
header('Location: index.php#orders');
exit;
?>
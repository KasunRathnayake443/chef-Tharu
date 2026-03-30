<?php
require_once 'auth.php';
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$name = trim($_POST['name'] ?? '');
$category_id = (int)($_POST['category_id'] ?? 0);
$description = trim($_POST['description'] ?? '');
$price = (float)($_POST['price'] ?? 0);
$available = isset($_POST['available']) ? 1 : 0;

if ($name === '' || $category_id <= 0 || $price <= 0) {
    $_SESSION['flash_error'] = 'Name, category and valid price are required.';
    header('Location: index.php#foods');
    exit;
}

$imageName = null;

if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $uploadDir = dirname(__DIR__) . '/uploads/foods/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $tmpName = $_FILES['image']['tmp_name'];
    $originalName = $_FILES['image']['name'];
    $fileSize = $_FILES['image']['size'];

    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allowed, true)) {
        $_SESSION['flash_error'] = 'Only JPG, JPEG, PNG, and WEBP images are allowed.';
        header('Location: index.php#foods');
        exit;
    }

    if ($fileSize > 5 * 1024 * 1024) {
        $_SESSION['flash_error'] = 'Image must be less than 5MB.';
        header('Location: index.php#foods');
        exit;
    }

    $imageName = 'food_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destination = $uploadDir . $imageName;

    if (!move_uploaded_file($tmpName, $destination)) {
        $_SESSION['flash_error'] = 'Failed to upload image.';
        header('Location: index.php#foods');
        exit;
    }
}

$stmt = $pdo->prepare("
    INSERT INTO foods (category_id, name, description, price, image, available)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->execute([$category_id, $name, $description, $price, $imageName, $available]);

$_SESSION['flash_success'] = 'Food added successfully.';
header('Location: index.php#foods');
exit;
?>
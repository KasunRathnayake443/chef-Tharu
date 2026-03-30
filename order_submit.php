<?php
require_once 'config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request.'
    ]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$customer = $data['customer'] ?? [];
$items = $data['items'] ?? [];

$name = trim($customer['name'] ?? '');
$phone = trim($customer['phone'] ?? '');
$email = trim($customer['email'] ?? '');
$address = trim($customer['address'] ?? '');
$notes = trim($customer['notes'] ?? '');

if ($name === '' || $phone === '' || $address === '') {
    echo json_encode([
        'success' => false,
        'error' => 'Name, phone and address are required.'
    ]);
    exit;
}

if (!$items || !is_array($items)) {
    echo json_encode([
        'success' => false,
        'error' => 'Your cart is empty.'
    ]);
    exit;
}

$foodIds = [];
foreach ($items as $item) {
    $foodIds[] = (int)($item['id'] ?? 0);
}
$foodIds = array_values(array_filter($foodIds));

if (!$foodIds) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid cart items.'
    ]);
    exit;
}

$placeholders = implode(',', array_fill(0, count($foodIds), '?'));
$stmt = $pdo->prepare("
    SELECT id, name, price
    FROM foods
    WHERE id IN ($placeholders) AND available = 1
");
$stmt->execute($foodIds);
$dbFoods = $stmt->fetchAll();

$foodMap = [];
foreach ($dbFoods as $food) {
    $foodMap[$food['id']] = $food;
}

$total = 0;
$finalItems = [];

foreach ($items as $item) {
    $foodId = (int)($item['id'] ?? 0);
    $qty = (int)($item['qty'] ?? 0);

    if ($foodId <= 0 || $qty <= 0 || !isset($foodMap[$foodId])) {
        continue;
    }

    $food = $foodMap[$foodId];
    $lineTotal = $food['price'] * $qty;
    $total += $lineTotal;

    $finalItems[] = [
        'food_id' => $food['id'],
        'item_name' => $food['name'],
        'price' => $food['price'],
        'qty' => $qty
    ];
}

if (!$finalItems) {
    echo json_encode([
        'success' => false,
        'error' => 'No valid items found in cart.'
    ]);
    exit;
}

$orderRef = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO orders (
            order_ref,
            cust_name,
            cust_phone,
            cust_email,
            cust_address,
            cust_notes,
            total,
            status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
    ");
    $stmt->execute([
        $orderRef,
        $name,
        $phone,
        $email,
        $address,
        $notes,
        $total
    ]);

    $orderId = $pdo->lastInsertId();

    $itemStmt = $pdo->prepare("
        INSERT INTO order_items (
            order_id,
            food_id,
            item_name,
            price,
            qty
        ) VALUES (?, ?, ?, ?, ?)
    ");

    foreach ($finalItems as $item) {
        $itemStmt->execute([
            $orderId,
            $item['food_id'],
            $item['item_name'],
            $item['price'],
            $item['qty']
        ]);
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'order_ref' => $orderRef,
        'total' => number_format($total, 2, '.', '')
    ]);
} catch (Exception $e) {
    $pdo->rollBack();

    echo json_encode([
        'success' => false,
        'error' => 'Failed to save order.'
    ]);
}
?>
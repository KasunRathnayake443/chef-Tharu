<?php
require_once 'auth.php';
require_once '../config/db.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['flash_error'] = 'Invalid order selected.';
    header('Location: index.php#orders');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['flash_error'] = 'Order not found.';
    header('Location: index.php#orders');
    exit;
}

$itemStmt = $pdo->prepare("
    SELECT *
    FROM order_items
    WHERE order_id = ?
    ORDER BY id ASC
");
$itemStmt->execute([$id]);
$orderItems = $itemStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Order - <?php echo htmlspecialchars($order['order_ref']); ?></title>
<style>
    :root{
        --gold:#c9a654;
        --bg:#111;
        --card:#1a1a1a;
        --text:#f4f4f4;
        --muted:#aaa;
        --border:#333;
    }
    *{box-sizing:border-box}
    body{margin:0;font-family:Arial,sans-serif;background:var(--bg);color:var(--text);padding:24px}
    .wrap{max-width:1000px;margin:0 auto}
    .card{
        background:var(--card);
        border:1px solid var(--border);
        border-radius:14px;
        padding:20px;
        margin-bottom:20px;
    }
    h1,h2{margin-top:0}
    .top-actions{margin-bottom:20px}
    .btn{
        display:inline-block;
        padding:10px 14px;
        border-radius:8px;
        background:var(--gold);
        color:#111;
        font-weight:bold;
        text-decoration:none;
    }
    .grid{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:16px;
    }
    table{width:100%;border-collapse:collapse}
    th,td{padding:12px;border-bottom:1px solid var(--border);text-align:left}
    th{color:var(--muted)}
    .muted{color:var(--muted)}
    @media (max-width:768px){
        .grid{grid-template-columns:1fr}
    }
</style>
</head>
<body>
<div class="wrap">
    <div class="top-actions">
        <a href="index.php#orders" class="btn">← Back to Orders</a>
    </div>

    <div class="card">
        <h1>Order <?php echo htmlspecialchars($order['order_ref']); ?></h1>
        <div class="grid">
            <div>
                <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['cust_name']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['cust_phone']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['cust_email'] ?: '-'); ?></p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars(ucfirst($order['status'])); ?></p>
            </div>
            <div>
                <p><strong>Date:</strong> <?php echo htmlspecialchars($order['created_at']); ?></p>
                <p><strong>Total:</strong> Rs. <?php echo number_format($order['total'], 2); ?></p>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>Delivery Details</h2>
        <p><strong>Address:</strong><br><?php echo nl2br(htmlspecialchars($order['cust_address'])); ?></p>
        <p><strong>Notes:</strong><br><?php echo $order['cust_notes'] ? nl2br(htmlspecialchars($order['cust_notes'])) : '<span class="muted">No notes</span>'; ?></p>
    </div>

    <div class="card">
        <h2>Order Items</h2>
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orderItems): ?>
                    <?php foreach ($orderItems as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                            <td>Rs. <?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo (int)$item['qty']; ?></td>
                            <td>Rs. <?php echo number_format($item['price'] * $item['qty'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No order items found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
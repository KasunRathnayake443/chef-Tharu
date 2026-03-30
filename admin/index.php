<?php
require_once 'auth.php';
require_once '../config/db.php';

$categories = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC, name ASC")->fetchAll();

$foods = $pdo->query("
    SELECT foods.*, categories.name AS category_name
    FROM foods
    INNER JOIN categories ON categories.id = foods.category_id
    ORDER BY foods.id DESC
")->fetchAll();

$orders = $pdo->query("
    SELECT * FROM orders
    ORDER BY created_at DESC
")->fetchAll();

$admins = $pdo->query("
    SELECT id, username, full_name, created_at
    FROM admins
    ORDER BY id DESC
")->fetchAll();

$totalOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalFoods = (int)$pdo->query("SELECT COUNT(*) FROM foods")->fetchColumn();
$totalCategories = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$pendingOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();

$flashSuccess = $_SESSION['flash_success'] ?? '';
$flashError = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chef Tharu Admin</title>
<style>
    :root{
        --gold:#c9a654;
        --bg:#111;
        --card:#1a1a1a;
        --card2:#222;
        --text:#f4f4f4;
        --muted:#aaa;
        --border:#333;
        --green:#1f7a4d;
        --red:#8a2b2b;
        --blue:#1f4f7a;
    }
    *{box-sizing:border-box}
    body{margin:0;font-family:Arial,sans-serif;background:var(--bg);color:var(--text)}
    a{text-decoration:none}
    .layout{display:flex;min-height:100vh}
    .sidebar{
        width:250px;background:#181818;border-right:1px solid var(--border);
        padding:20px;position:sticky;top:0;height:100vh
    }
    .brand{font-size:26px;font-weight:bold;color:var(--gold);margin-bottom:30px}
    .nav a{
        display:block;padding:12px 14px;margin-bottom:8px;border-radius:8px;
        color:#ddd;background:transparent
    }
    .nav a:hover{background:#232323}
    .side-actions{margin-top:30px;display:grid;gap:10px}
    .btn-link{
        display:block;text-align:center;padding:12px;border-radius:8px;font-weight:bold
    }
    .btn-site{background:var(--gold);color:#111}
    .btn-logout{background:#2a2a2a;color:#fff}
    .main{flex:1;padding:24px}
    .topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px}
    .cards{
        display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px
    }
    .card{
        background:var(--card);border:1px solid var(--border);padding:20px;border-radius:14px
    }
    .card h3{margin:0 0 8px;font-size:14px;color:var(--muted)}
    .card .num{font-size:34px;font-weight:bold;color:var(--gold)}
    .section{
        background:var(--card);border:1px solid var(--border);padding:20px;border-radius:14px;
        margin-bottom:24px
    }
    .section h2{margin:0 0 16px}
    table{width:100%;border-collapse:collapse}
    th,td{padding:12px;border-bottom:1px solid var(--border);text-align:left;vertical-align:top}
    th{color:var(--muted);font-size:13px}
    .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:24px}
    .field{margin-bottom:14px}
    label{display:block;margin-bottom:6px;color:#ccc;font-size:14px}
    input, textarea, select{
        width:100%;padding:12px;border:1px solid var(--border);border-radius:10px;
        background:#101010;color:#fff
    }
    textarea{min-height:100px;resize:vertical}
    .btn{
        padding:12px 18px;border:none;border-radius:10px;background:var(--gold);
        color:#111;font-weight:bold;cursor:pointer
    }
    .btn-sm{
        padding:8px 12px;border:none;border-radius:8px;font-weight:bold;cursor:pointer;
        font-size:12px
    }
    .btn-edit{background:var(--blue);color:#d8ecff}
    .btn-delete{background:var(--red);color:#fff}
    .inline-form{display:inline}
    .flash{
        padding:12px 14px;border-radius:10px;margin-bottom:18px
    }
    .flash.success{background:#163322;color:#b9f5d0}
    .flash.error{background:#351818;color:#ffc4c4}
    .food-thumb{
        width:60px;height:60px;object-fit:cover;border-radius:8px;background:#2b2b2b
    }
    .preview-wrap{
        margin-top:10px;
        display:none;
    }
    .preview-wrap img{
        width:120px;height:120px;object-fit:cover;border-radius:10px;border:1px solid var(--border)
    }
    .badge{
        display:inline-block;padding:5px 10px;border-radius:999px;font-size:12px
    }
    .available{background:#173524;color:#a7f0c4}
    .hidden{background:#382121;color:#ffb8b8}
    .status-pending{background:#3d3215;color:#f6dc8c}
    .status-confirmed,.status-delivered{background:#173524;color:#a7f0c4}
    .status-preparing,.status-ready{background:#1c2f44;color:#b9dcff}
    .status-cancelled{background:#382121;color:#ffb8b8}
    .thumb-large{
        width:120px;height:120px;object-fit:cover;border-radius:10px;border:1px solid var(--border);display:block
    }
    .actions-cell{
        display:flex;gap:8px;flex-wrap:wrap
    }
    .modal{
        display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);
        align-items:center;justify-content:center;z-index:999;padding:20px
    }
    .modal.show{display:flex}
    .modal-box{
        width:100%;max-width:700px;background:var(--card);border:1px solid var(--border);
        border-radius:16px;padding:24px;max-height:90vh;overflow:auto
    }
    .modal-head{
        display:flex;justify-content:space-between;align-items:center;margin-bottom:20px
    }
    .modal-close{
        background:#2a2a2a;color:#fff;border:none;border-radius:8px;padding:10px 14px;cursor:pointer
    }
    @media (max-width: 1100px){
        .cards{grid-template-columns:repeat(2,1fr)}
        .grid-2{grid-template-columns:1fr}
    }
    @media (max-width: 768px){
        .layout{display:block}
        .sidebar{width:100%;height:auto;position:relative}
        .cards{grid-template-columns:1fr}
        .main{padding:16px}
    }
</style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="brand">Chef Tharu</div>

        <div class="nav">
            <a href="#dashboard">Dashboard</a>
            <a href="#orders">Orders</a>
            <a href="#categories">Categories</a>
            <a href="#foods">Foods</a>
            <a href="#admins">Admins</a>
        </div>

        <div class="side-actions">
            <a href="../index.php" target="_blank" class="btn-link btn-site">Visit Site</a>
            <a href="logout.php" class="btn-link btn-logout">Logout</a>
        </div>
    </aside>

    <main class="main">
        <div class="topbar">
            <div>
                <h1 style="margin:0">Admin Dashboard</h1>
                <div style="color:var(--muted);margin-top:4px">
                    Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
                </div>
            </div>
        </div>

        <?php if ($flashSuccess): ?>
            <div class="flash success"><?php echo htmlspecialchars($flashSuccess); ?></div>
        <?php endif; ?>

        <?php if ($flashError): ?>
            <div class="flash error"><?php echo htmlspecialchars($flashError); ?></div>
        <?php endif; ?>

        <section id="dashboard" class="cards">
            <div class="card">
                <h3>Total Orders</h3>
                <div class="num"><?php echo $totalOrders; ?></div>
            </div>
            <div class="card">
                <h3>Pending Orders</h3>
                <div class="num"><?php echo $pendingOrders; ?></div>
            </div>
            <div class="card">
                <h3>Total Foods</h3>
                <div class="num"><?php echo $totalFoods; ?></div>
            </div>
            <div class="card">
                <h3>Total Categories</h3>
                <div class="num"><?php echo $totalCategories; ?></div>
            </div>
        </section>

        <div class="grid-2">
            <section id="categories" class="section">
                <h2>Add Category</h2>
                <form action="save_category.php" method="POST">
                    <div class="field">
                        <label>Category Name</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="field">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" value="0">
                    </div>
                    <button type="submit" class="btn">Add Category</button>
                </form>

                <hr style="border-color:var(--border);margin:22px 0">

                <h2>Category List</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Sort Order</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($categories): ?>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td><?php echo $cat['id']; ?></td>
                                    <td><?php echo htmlspecialchars($cat['name']); ?></td>
                                    <td><?php echo $cat['sort_order']; ?></td>
                                    <td>
                                        <div class="actions-cell">
                                            <button
                                                type="button"
                                                class="btn-sm btn-edit"
                                                onclick="openEditCategory(
                                                    '<?php echo $cat['id']; ?>',
                                                    '<?php echo htmlspecialchars($cat['name'], ENT_QUOTES); ?>',
                                                    '<?php echo $cat['sort_order']; ?>'
                                                )"
                                            >
                                                Edit
                                            </button>

                                            <form class="inline-form" action="delete_category.php" method="POST" onsubmit="return confirm('Delete this category? Foods under it may also be deleted.');">
                                                <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                                <button type="submit" class="btn-sm btn-delete">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">No categories yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>

            <section id="foods" class="section">
                <h2>Add Food</h2>
                <form action="save_food.php" method="POST" enctype="multipart/form-data">
                    <div class="field">
                        <label>Food Name</label>
                        <input type="text" name="name" required>
                    </div>

                    <div class="field">
                        <label>Category</label>
                        <select name="category_id" required>
                            <option value="">Select category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="field">
                        <label>Description</label>
                        <textarea name="description"></textarea>
                    </div>

                    <div class="field">
                        <label>Price</label>
                        <input type="number" name="price" min="0" step="0.01" required>
                    </div>

                    <div class="field">
                        <label>Food Image</label>
                        <input type="file" name="image" id="foodImageInput" accept=".jpg,.jpeg,.png,.webp">
                        <div class="preview-wrap" id="previewWrap">
                            <img id="previewImage" src="" alt="Preview">
                        </div>
                    </div>

                    <div class="field">
                        <label>
                            <input type="checkbox" name="available" checked style="width:auto;margin-right:8px">
                            Available
                        </label>
                    </div>

                    <button type="submit" class="btn">Add Food</button>
                </form>
            </section>
        </div>

        <section class="section">
            <h2>Food List</h2>
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($foods): ?>
                        <?php foreach ($foods as $food): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($food['image'])): ?>
                                        <img class="food-thumb" src="../uploads/foods/<?php echo htmlspecialchars($food['image']); ?>" alt="">
                                    <?php else: ?>
                                        <div class="food-thumb"></div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($food['name']); ?></td>
                                <td><?php echo htmlspecialchars($food['category_name']); ?></td>
                                <td>Rs. <?php echo number_format($food['price'], 2); ?></td>
                                <td>
                                    <?php if ($food['available']): ?>
                                        <span class="badge available">Available</span>
                                    <?php else: ?>
                                        <span class="badge hidden">Hidden</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="actions-cell">
                                        <button
                                            type="button"
                                            class="btn-sm btn-edit"
                                            onclick="openEditFood(
                                                '<?php echo $food['id']; ?>',
                                                '<?php echo htmlspecialchars($food['name'], ENT_QUOTES); ?>',
                                                '<?php echo $food['category_id']; ?>',
                                                '<?php echo htmlspecialchars($food['description'] ?? '', ENT_QUOTES); ?>',
                                                '<?php echo $food['price']; ?>',
                                                '<?php echo (int)$food['available']; ?>',
                                                '<?php echo htmlspecialchars($food['image'] ?? '', ENT_QUOTES); ?>'
                                            )"
                                        >
                                            Edit
                                        </button>

                                        <form class="inline-form" action="delete_food.php" method="POST" onsubmit="return confirm('Delete this food item?');">
                                            <input type="hidden" name="id" value="<?php echo $food['id']; ?>">
                                            <button type="submit" class="btn-sm btn-delete">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No foods yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <section id="orders" class="section">
            <h2>Orders</h2>
            <table>
                <thead>
                    <tr>
                        <th>Ref</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($orders): ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['order_ref']); ?></td>
                                <td><?php echo htmlspecialchars($order['cust_name']); ?></td>
                                <td><?php echo htmlspecialchars($order['cust_phone']); ?></td>
                                <td>Rs. <?php echo number_format($order['total'], 2); ?></td>
                                <td>
                                    <span class="badge status-<?php echo htmlspecialchars($order['status']); ?>">
                                        <?php echo htmlspecialchars(ucfirst($order['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                                <td>
                                    <div class="actions-cell">
                                        <a class="btn-sm btn-edit" href="view_order.php?id=<?php echo $order['id']; ?>">View</a>

                                        <button
                                            type="button"
                                            class="btn-sm btn-edit"
                                            onclick="openOrderStatusModal(
                                                '<?php echo $order['id']; ?>',
                                                '<?php echo htmlspecialchars($order['order_ref'], ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars($order['status'], ENT_QUOTES); ?>'
                                            )"
                                        >
                                            Status
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">No orders yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

                
        <section id="admins" class="section">
            <h2>Add New Admin</h2>

            <form action="save_admin.php" method="POST">
                <div class="field">
                    <label>Full Name</label>
                    <input type="text" name="full_name">
                </div>

                <div class="field">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>

                <div class="field">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>

                <div class="field">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required>
                </div>

                <button type="submit" class="btn">Add Admin</button>
            </form>

            <hr style="border-color:var(--border);margin:22px 0">

            <h2>Admin List</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($admins): ?>
                        <?php foreach ($admins as $admin): ?>
                            <tr>
                                <td><?php echo $admin['id']; ?></td>
                                <td><?php echo htmlspecialchars($admin['full_name'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($admin['username']); ?></td>
                                <td><?php echo htmlspecialchars($admin['created_at']); ?></td>
                                <td>
                                    <div class="actions-cell">
                                        <button
                                            type="button"
                                            class="btn-sm btn-edit"
                                            onclick="openEditAdmin(
                                                '<?php echo $admin['id']; ?>',
                                                '<?php echo htmlspecialchars($admin['full_name'] ?? '', ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars($admin['username'], ENT_QUOTES); ?>'
                                            )"
                                        >
                                            Edit
                                        </button>

                                        <?php if ((int)$admin['id'] !== (int)$_SESSION['admin_id']): ?>
                                            <form class="inline-form" action="delete_admin.php" method="POST" onsubmit="return confirm('Delete this admin?');">
                                                <input type="hidden" name="id" value="<?php echo $admin['id']; ?>">
                                                <button type="submit" class="btn-sm btn-delete">Delete</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No admins found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

    </main>
</div>

<div class="modal" id="editCategoryModal">
    <div class="modal-box">
        <div class="modal-head">
            <h2 style="margin:0">Edit Category</h2>
            <button type="button" class="modal-close" onclick="closeModal('editCategoryModal')">Close</button>
        </div>

        <form action="update_category.php" method="POST">
            <input type="hidden" name="id" id="editCategoryId">

            <div class="field">
                <label>Category Name</label>
                <input type="text" name="name" id="editCategoryName" required>
            </div>

            <div class="field">
                <label>Sort Order</label>
                <input type="number" name="sort_order" id="editCategorySort" value="0">
            </div>

            <button type="submit" class="btn">Update Category</button>
        </form>
    </div>
</div>

<div class="modal" id="editFoodModal">
    <div class="modal-box">
        <div class="modal-head">
            <h2 style="margin:0">Edit Food</h2>
            <button type="button" class="modal-close" onclick="closeModal('editFoodModal')">Close</button>
        </div>

        <form action="update_food.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" id="editFoodId">
            <input type="hidden" name="old_image" id="editFoodOldImage">

            <div class="field">
                <label>Food Name</label>
                <input type="text" name="name" id="editFoodName" required>
            </div>

            <div class="field">
                <label>Category</label>
                <select name="category_id" id="editFoodCategory" required>
                    <option value="">Select category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label>Description</label>
                <textarea name="description" id="editFoodDescription"></textarea>
            </div>

            <div class="field">
                <label>Price</label>
                <input type="number" name="price" id="editFoodPrice" min="0" step="0.01" required>
            </div>

            <div class="field">
                <label>Replace Image</label>
                <input type="file" name="image" id="editFoodImageInput" accept=".jpg,.jpeg,.png,.webp">
            </div>

            <div class="field">
                <label>Current / New Preview</label>
                <img id="editFoodPreview" class="thumb-large" src="" alt="Food image preview">
            </div>

            <div class="field">
                <label>
                    <input type="checkbox" name="available" id="editFoodAvailable" style="width:auto;margin-right:8px">
                    Available
                </label>
            </div>

            <button type="submit" class="btn">Update Food</button>
        </form>
    </div>
</div>

<div class="modal" id="editAdminModal">
    <div class="modal-box">
        <div class="modal-head">
            <h2 style="margin:0">Edit Admin</h2>
            <button type="button" class="modal-close" onclick="closeModal('editAdminModal')">Close</button>
        </div>

        <form action="update_admin.php" method="POST">
            <input type="hidden" name="id" id="editAdminId">

            <div class="field">
                <label>Full Name</label>
                <input type="text" name="full_name" id="editAdminFullName">
            </div>

            <div class="field">
                <label>Username</label>
                <input type="text" name="username" id="editAdminUsername" required>
            </div>

            <div class="field">
                <label>New Password</label>
                <input type="password" name="password" id="editAdminPassword">
            </div>

            <div class="field">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" id="editAdminConfirmPassword">
            </div>

            <p style="color:var(--muted);font-size:13px;margin-top:-6px;margin-bottom:16px;">
                Leave password fields empty if you do not want to change the password.
            </p>

            <button type="submit" class="btn">Update Admin</button>
        </form>
    </div>
</div>

<div class="modal" id="orderStatusModal">
    <div class="modal-box">
        <div class="modal-head">
            <h2 style="margin:0">Update Order Status</h2>
            <button type="button" class="modal-close" onclick="closeModal('orderStatusModal')">Close</button>
        </div>

        <form action="update_order_status.php" method="POST">
            <input type="hidden" name="id" id="orderStatusId">

            <div class="field">
                <label>Order Reference</label>
                <input type="text" id="orderStatusRef" readonly>
            </div>

            <div class="field">
                <label>Status</label>
                <select name="status" id="orderStatusValue" required>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="preparing">Preparing</option>
                    <option value="ready">Ready</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <button type="submit" class="btn">Update Status</button>
        </form>
    </div>
</div>

<script>
const foodImageInput = document.getElementById('foodImageInput');
const previewWrap = document.getElementById('previewWrap');
const previewImage = document.getElementById('previewImage');

foodImageInput?.addEventListener('change', function () {
    const file = this.files[0];

    if (!file) {
        previewWrap.style.display = 'none';
        previewImage.src = '';
        return;
    }

    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

    if (!allowedTypes.includes(file.type)) {
        alert('Only JPG, PNG, and WEBP images are allowed.');
        this.value = '';
        previewWrap.style.display = 'none';
        previewImage.src = '';
        return;
    }

    const reader = new FileReader();

    reader.onload = function (e) {
        previewImage.src = e.target.result;
        previewWrap.style.display = 'block';
    };

    reader.readAsDataURL(file);
});

function openModal(id) {
    document.getElementById(id).classList.add('show');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('show');
}

function openEditCategory(id, name, sortOrder) {
    document.getElementById('editCategoryId').value = id;
    document.getElementById('editCategoryName').value = name;
    document.getElementById('editCategorySort').value = sortOrder;
    openModal('editCategoryModal');
}

function openEditFood(id, name, categoryId, description, price, available, image) {
    document.getElementById('editFoodId').value = id;
    document.getElementById('editFoodName').value = name;
    document.getElementById('editFoodCategory').value = categoryId;
    document.getElementById('editFoodDescription').value = description;
    document.getElementById('editFoodPrice').value = price;
    document.getElementById('editFoodAvailable').checked = Number(available) === 1;
    document.getElementById('editFoodOldImage').value = image;

    const preview = document.getElementById('editFoodPreview');
    if (image) {
        preview.src = '../uploads/foods/' + image;
    } else {
        preview.src = '';
    }

    document.getElementById('editFoodImageInput').value = '';
    openModal('editFoodModal');
}

document.getElementById('editFoodImageInput')?.addEventListener('change', function () {
    const file = this.files[0];
    const preview = document.getElementById('editFoodPreview');

    if (!file) {
        return;
    }

    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

    if (!allowedTypes.includes(file.type)) {
        alert('Only JPG, PNG, and WEBP images are allowed.');
        this.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function (e) {
        preview.src = e.target.result;
    };
    reader.readAsDataURL(file);
});
</script>

<script>
function openEditAdmin(id, fullName, username) {
    document.getElementById('editAdminId').value = id;
    document.getElementById('editAdminFullName').value = fullName;
    document.getElementById('editAdminUsername').value = username;
    document.getElementById('editAdminPassword').value = '';
    document.getElementById('editAdminConfirmPassword').value = '';
    openModal('editAdminModal');
}
</script>

<script>
function openOrderStatusModal(id, orderRef, status) {
    document.getElementById('orderStatusId').value = id;
    document.getElementById('orderStatusRef').value = orderRef;
    document.getElementById('orderStatusValue').value = status;
    openModal('orderStatusModal');
}
</script>

</body>
</html>
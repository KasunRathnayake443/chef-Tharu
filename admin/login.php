<?php
session_start();
require_once '../config/db.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid login details.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - Chef Tharu</title>
<style>
    body{
        margin:0;font-family:Arial,sans-serif;background:#111;color:#fff;
        min-height:100vh;display:flex;align-items:center;justify-content:center;
    }
    .box{
        width:100%;max-width:400px;background:#1b1b1b;padding:30px;border-radius:12px;
        box-shadow:0 10px 30px rgba(0,0,0,.35);
    }
    h1{margin:0 0 20px;font-size:28px}
    .field{margin-bottom:15px}
    label{display:block;margin-bottom:6px;font-size:14px;color:#ccc}
    input{
        width:100%;padding:12px;border:1px solid #333;border-radius:8px;
        background:#0f0f0f;color:#fff;box-sizing:border-box;
    }
    button{
        width:100%;padding:12px;border:none;border-radius:8px;
        background:#c9a654;color:#111;font-weight:bold;cursor:pointer;
    }
    .error{background:#3a1616;color:#ffb3b3;padding:10px;border-radius:8px;margin-bottom:15px}
</style>
</head>
<body>
    <div class="box">
        <h1>Admin Login</h1>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="field">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>

            <div class="field">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
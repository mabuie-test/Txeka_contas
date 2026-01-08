<?php
// admin/login.php
session_start();
require_once __DIR__ . '/../lib/functions.php';
$pdo = get_pdo();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email === '' || $password === '') $error = 'Preencha email e password';
    else {
        $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $u = $stmt->fetch();
        if ($u && password_verify($password, $u['password_hash'])) {
            $_SESSION['admin_user'] = $u['id'];
            header('Location: dashboard.php');
            exit;
        } else $error = 'Credenciais inválidas';
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8"/>
<title>Admin Txeka — Login</title>
<link rel="stylesheet" href="../public/css/style.css">
</head>
<body class="admin-login">
<div class="card center">
    <h2>Admin Txeka — Entrar</h2>
    <?php if ($error): ?><div class="alert"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="post">
        <label>Email</label>
        <input type="email" name="email" required />
        <label>Password</label>
        <input type="password" name="password" required />
        <button type="submit" class="btn">Entrar</button>
    </form>
</div>
</body>
</html>


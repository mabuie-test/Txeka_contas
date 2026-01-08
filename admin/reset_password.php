<?php
// admin/reset_password.php
require_once __DIR__ . '/../lib/functions.php';
$pdo = get_pdo();

$token = $_GET['token'] ?? ($_POST['token'] ?? null);
$message = '';
$ok = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? null;
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    if (!$token || $password === '' || $password !== $password2) {
        $message = 'Dados inválidos ou passwords não coincidem.';
    } else {
        $stmt = $pdo->prepare("SELECT id, reset_token_created_at FROM users WHERE reset_token = ? LIMIT 1");
        $stmt->execute([$token]);
        $u = $stmt->fetch();
        if (!$u) {
            $message = 'Token inválido.';
        } else {
            // verificar expiração (1 hora)
            $created = $u['reset_token_created_at'];
            if ($created) {
                $created_ts = strtotime($created);
                if (time() - $created_ts > 3600) {
                    $message = 'Token expirado.';
                }
            }
            if ($message === '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt2 = $pdo->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_created_at = NULL WHERE id = ?");
                $stmt2->execute([$hash, $u['id']]);
                $ok = true;
                $message = 'Password reposta com sucesso. Pode agora entrar.';
            }
        }
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"/><title>Repor password</title><link rel="stylesheet" href="../public/css/style.css"></head>
<body>
<main class="container">
  <div class="card center">
    <h2>Repor password</h2>
    <?php if ($message): ?><div class="alert"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <?php if ($ok): ?>
      <p><a href="login.php" class="btn">Ir para login</a></p>
    <?php else: ?>
      <form method="post">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>"/>
        <label>Nova password</label>
        <input type="password" name="password" required />
        <label>Confirmar password</label>
        <input type="password" name="password2" required />
        <button class="btn" type="submit">Repor password</button>
      </form>
    <?php endif; ?>
  </div>
</main>
</body>
</html>


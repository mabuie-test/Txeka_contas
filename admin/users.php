<?php
require_once __DIR__ . '/_auth_check.php';
require_once __DIR__ . '/../lib/functions.php';
$pdo = get_pdo();

$stmt = $pdo->query("SELECT id, name, email, created_at FROM users ORDER BY created_at DESC LIMIT 1000");
$rows = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"/><title>Utilizadores</title><link rel="stylesheet" href="../public/css/style.css"></head>
<body>
<?php include 'nav.php'; ?>
<main class="container">
  <h1>Utilizadores</h1>
  <table class="table">
    <thead><tr><th>ID</th><th>Nome</th><th>Email</th><th>Registo</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?php echo $r['id'];?></td>
        <td><?php echo htmlspecialchars($r['name']);?></td>
        <td><?php echo htmlspecialchars($r['email']);?></td>
        <td><?php echo $r['created_at'];?></td>
      </tr>
    <?php endforeach;?>
    </tbody>
  </table>
</main>
</body>
</html>


<?php
require_once __DIR__ . '/_auth_check.php';
require_once __DIR__ . '/../lib/functions.php';
$pdo = get_pdo();

$userid = isset($_GET['userId']) ? (int)$_GET['userId'] : null;
$sql = "SELECT d.*, u.email FROM debts d LEFT JOIN users u ON u.id = d.user_id";
$params = [];
$where = [];
if ($userid) { $where[] = "d.user_id = ?"; $params[] = $userid; }
if ($where) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY created_at DESC LIMIT 1000";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"/><title>Dívidas</title><link rel="stylesheet" href="../public/css/style.css"></head>
<body>
<?php include 'nav.php'; ?>
<main class="container">
  <h1>Dívidas</h1>
  <table class="table">
    <thead><tr><th>ID</th><th>User</th><th>Contra-parte</th><th>Tipo</th><th>Original</th><th>Em aberto</th><th>Data prevista</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?php echo $r['id'];?></td>
        <td><?php echo htmlspecialchars($r['email']);?></td>
        <td><?php echo htmlspecialchars($r['counterparty']);?></td>
        <td><?php echo htmlspecialchars($r['type']);?></td>
        <td><?php echo number_format($r['amount_original'],2);?></td>
        <td><?php echo number_format($r['amount_outstanding'],2);?></td>
        <td><?php echo $r['due_date'] ? date('d/m/Y', intval($r['due_date']/1000)) : '';?></td>
        <td><?php echo htmlspecialchars($r['status']);?></td>
      </tr>
    <?php endforeach;?>
    </tbody>
  </table>
</main>
</body>
</html>

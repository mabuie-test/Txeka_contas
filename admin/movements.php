<?php
require_once __DIR__ . '/_auth_check.php';
require_once __DIR__ . '/../lib/functions.php';
$pdo = get_pdo();

$userid = isset($_GET['userId']) ? (int)$_GET['userId'] : null;
$from = isset($_GET['from']) ? (int)$_GET['from'] : 0;
$to = isset($_GET['to']) ? (int)$_GET['to'] : time()*1000;

$where = [];
$params = [];
if ($userid) { $where[] = "user_id = ?"; $params[] = $userid; }
$where[] = "timestamp >= ? AND timestamp <= ?";
$params[] = $from;
$params[] = $to;

$sql = "SELECT m.*, u.email FROM movements m LEFT JOIN users u ON u.id = m.user_id";
if ($where) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY timestamp DESC LIMIT 1000";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"/><title>Movimentos</title><link rel="stylesheet" href="../public/css/style.css"></head>
<body>
<?php include 'nav.php'; ?>
<main class="container">
  <h1>Movimentos</h1>
  <form method="get" class="filter">
    <label>UserId: <input type="number" name="userId" value="<?php echo htmlspecialchars($userid); ?>"></label>
    <label>From (ms timestamp): <input type="text" name="from" value="<?php echo htmlspecialchars($from); ?>"></label>
    <label>To (ms): <input type="text" name="to" value="<?php echo htmlspecialchars($to); ?>"></label>
    <button type="submit" class="btn">Filtrar</button>
  </form>

  <table class="table">
    <thead><tr><th>ID</th><th>User</th><th>Tipo</th><th>Categoria</th><th>Valor</th><th>Timestamp</th><th>Evidence</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?php echo $r['id'];?></td>
        <td><?php echo htmlspecialchars($r['email']);?></td>
        <td><?php echo htmlspecialchars($r['type']);?></td>
        <td><?php echo htmlspecialchars($r['category']);?></td>
        <td><?php echo number_format($r['amount'],2);?></td>
        <td><?php echo date('d/m/Y H:i', intval($r['timestamp']/1000));?></td>
        <td><?php if ($r['evidence_url']): ?><a href="<?php echo htmlspecialchars($r['evidence_url']);?>" target="_blank">ver</a><?php endif;?></td>
      </tr>
    <?php endforeach;?>
    </tbody>
  </table>
</main>
</body>
</html>


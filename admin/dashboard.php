<?php
require_once __DIR__ . '/_auth_check.php';
require_once __DIR__ . '/../lib/functions.php';
$pdo = get_pdo();

$totalUsers = $pdo->query("SELECT COUNT(*) as c FROM users")->fetch()['c'];
$totalMov = $pdo->query("SELECT COUNT(*) as c FROM movements")->fetch()['c'];
$totalDebts = $pdo->query("SELECT COUNT(*) as c FROM debts")->fetch()['c'];

$sth = $pdo->query("SELECT category, SUM(CASE WHEN type='entrada' THEN amount ELSE 0 END) as inflow, SUM(CASE WHEN type='saida' THEN amount ELSE 0 END) as outflow, (SUM(CASE WHEN type='entrada' THEN amount ELSE 0 END) - SUM(CASE WHEN type='saida' THEN amount ELSE 0 END)) as net FROM movements GROUP BY category ORDER BY net DESC LIMIT 10");
$categoryRows = $sth->fetchAll();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8"/>
<title>Admin — Dashboard</title>
<link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
<?php include 'nav.php'; ?>
<main class="container">
  <h1>Dashboard</h1>
  <div class="cards">
    <div class="card small">Utilizadores<br><strong><?php echo $totalUsers; ?></strong></div>
    <div class="card small">Movimentos<br><strong><?php echo $totalMov; ?></strong></div>
    <div class="card small">Dívidas<br><strong><?php echo $totalDebts; ?></strong></div>
  </div>

  <h2>Top categorias (net)</h2>
  <div class="chart">
    <?php
    $max = 1;
    foreach ($categoryRows as $r) if (abs($r['net'])>$max) $max = abs($r['net']);
    $h = count($categoryRows)*36 + 40;
    echo '<svg width="800" height="'.($h).'" xmlns="http://www.w3.org/2000/svg">';
    $y = 20;
    foreach ($categoryRows as $r) {
        $label = htmlspecialchars($r['category']);
        $val = (float)$r['net'];
        $w = ($val/$max) * 300;
        $color = $val>=0 ? '#2E8B57' : '#E53935';
        echo "<text x='10' y='".($y+14)."' font-size='12'>$label</text>";
        echo "<rect x='200' y='$y' width='".abs($w)."' height='18' fill='$color' />";
        echo "<text x='".(200+abs($w)+8)."' y='".($y+14)."' font-size='12'>".number_format($val,2)."</text>";
        $y += 36;
    }
    echo '</svg>';
    ?>
  </div>
</main>
</body>
</html>


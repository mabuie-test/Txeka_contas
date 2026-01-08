<?php
// api/movements.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../lib/functions.php';
$pdo = get_pdo();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error'=>'method']); exit; }

$userId = require_api_token(); // devolve id ou encerra
$body = json_decode(file_get_contents('php://input'), true);
if (!$body || empty($body['movements'])) { http_response_code(400); echo json_encode(['error'=>'no_data']); exit; }

$synced = [];
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare("INSERT INTO movements (user_id, client_local_id, amount, type, category, timestamp, note, payment_method, evidence_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($body['movements'] as $m) {
        $localId = isset($m['localId']) ? (int)$m['localId'] : null;
        $amount = (float)($m['amount'] ?? 0);
        $type = substr(trim($m['type'] ?? 'outro'), 0, 30);
        $category = substr(trim($m['category'] ?? ''), 0, 120);
        $ts = isset($m['timestamp']) ? (int)$m['timestamp'] : round(microtime(true)*1000);
        $note = $m['note'] ?? '';
        $pm = $m['paymentMethod'] ?? '';
        $evidence = $m['evidencePath'] ?? null;
        $stmt->execute([$userId, $localId, $amount, $type, $category, $ts, $note, $pm, $evidence]);
        $serverId = $pdo->lastInsertId();
        $synced[] = ['localId'=>$localId, 'serverId'=>$serverId];
    }
    $pdo->commit();
    echo json_encode(['ok'=>true, 'synced'=>$synced]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error'=>'server', 'message'=>$e->getMessage()]);
}


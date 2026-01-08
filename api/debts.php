<?php
// api/debts.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../lib/functions.php';
$pdo = get_pdo();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error'=>'method']); exit; }

$userId = require_api_token();
$body = json_decode(file_get_contents('php://input'), true);
if (!$body || empty($body['debts'])) { http_response_code(400); echo json_encode(['error'=>'no_data']); exit; }

$synced = [];
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare("INSERT INTO debts (user_id, client_local_id, counterparty, contact_phone, type, amount_original, amount_outstanding, created_at, due_date, status, note) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($body['debts'] as $d) {
        $localId = isset($d['localId']) ? (int)$d['localId'] : null;
        $counterparty = substr(trim($d['counterparty'] ?? ''), 0, 200);
        $phone = substr(trim($d['contactPhone'] ?? ''), 0, 60);
        $type = substr(trim($d['type'] ?? 'devido'), 0, 30);
        $orig = (float)($d['amountOriginal'] ?? 0);
        $out = (float)($d['amountOutstanding'] ?? $orig);
        $createdAt = isset($d['createdAt']) ? (int)$d['createdAt'] : round(microtime(true)*1000);
        $dueDate = isset($d['dueDate']) ? (int)$d['dueDate'] : null;
        $status = substr(trim($d['status'] ?? 'open'), 0, 30);
        $note = $d['note'] ?? '';
        $stmt->execute([$userId, $localId, $counterparty, $phone, $type, $orig, $out, $createdAt, $dueDate, $status, $note]);
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


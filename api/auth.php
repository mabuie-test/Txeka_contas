<?php
// api/auth.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../lib/functions.php';
$pdo = get_pdo();

$method = $_SERVER['REQUEST_METHOD'];
$body = json_decode(file_get_contents('php://input'), true) ?: [];

if ($method === 'POST') {
    $action = $_GET['action'] ?? 'login';
    if ($action === 'register') {
        $name = trim($body['name'] ?? '');
        $email = trim($body['email'] ?? '');
        $password = trim($body['password'] ?? '');
        if ($name === '' || $email === '' || $password === '') {
            http_response_code(400); echo json_encode(['error'=>'missing']); exit;
        }
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) { http_response_code(400); echo json_encode(['error'=>'email_exists']); exit; }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $token = generate_token(64);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, api_token) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $hash, $token]);
        $userId = $pdo->lastInsertId();
        echo json_encode(['token'=>$token, 'userId'=>$userId]);
        exit;
    } elseif ($action === 'login') {
        $email = trim($body['email'] ?? '');
        $password = trim($body['password'] ?? '');
        if ($email === '' || $password === '') { http_response_code(400); echo json_encode(['error'=>'missing']); exit; }
        $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $u = $stmt->fetch();
        if (!$u || !password_verify($password, $u['password_hash'])) { http_response_code(401); echo json_encode(['error'=>'invalid']); exit; }
        $token = generate_token(64);
        $stmt = $pdo->prepare("UPDATE users SET api_token = ? WHERE id = ?");
        $stmt->execute([$token, $u['id']]);
        echo json_encode(['token'=>$token, 'userId'=>$u['id']]);
        exit;
    } elseif ($action === 'recover') {
        $email = trim($body['email'] ?? '');
        if ($email === '') { http_response_code(400); echo json_encode(['ok'=>false]); exit; }
        $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $u = $stmt->fetch();
        if (!$u) { echo json_encode(['ok'=>true]); exit; } // não revela existência
        $token = generate_token(64);
        $now = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_created_at = ? WHERE id = ?");
        $stmt->execute([$token, $now, $u['id']]);
        $link = BASE_URL . '/admin/reset_password.php?token=' . urlencode($token);
        $msg = "Olá " . ($u['name'] ?? '') . ",\n\n";
        $msg .= "Recebemos um pedido de recuperação de palavra-passe para a sua conta.\n";
        $msg .= "Aceda ao link abaixo para repor a sua palavra-passe (válido por 1 hora):\n\n";
        $msg .= $link . "\n\n";
        $msg .= "Se não pediu a recuperação, ignore este email.\n\nAtenciosamente,\nEquipe Txeka";
        $ok = send_mail_smtp($email, MAIL_SUBJECT_RECOVER, $msg);
        if ($ok) echo json_encode(['ok'=>true]);
        else { http_response_code(500); echo json_encode(['ok'=>false,'error'=>'mail_failed']); }
        exit;
    }
}

http_response_code(405);
echo json_encode(['error'=>'method']);

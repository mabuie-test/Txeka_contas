<?php
// tools/send_test_mail.php
require_once __DIR__ . '/../lib/functions.php';

$to = $_GET['to'] ?? null;
if (!$to) {
    echo "Use: send_test_mail.php?to=you@domain.tld";
    exit;
}

$subject = "Teste SMTP Txeka " . date('Y-m-d H:i:s');
$body = "Isto é um teste de envio de email (PHPMailer manual).";

if (send_mail_smtp($to, $subject, $body)) {
    echo "OK - email enviado para $to\n";
} else {
    echo "ERRO - envio falhou. Verifica logs e configuração SMTP\n";
}


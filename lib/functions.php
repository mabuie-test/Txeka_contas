<?php
// lib/functions.php
require_once __DIR__ . '/../config.php';

function get_pdo() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

function api_bearer_token() {
    $hdr = null;
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) $hdr = trim($_SERVER['HTTP_AUTHORIZATION']);
    else if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        if (!empty($headers['Authorization'])) $hdr = trim($headers['Authorization']);
        elseif (!empty($headers['authorization'])) $hdr = trim($headers['authorization']);
    }
    if ($hdr && preg_match('/Bearer\s(\S+)/', $hdr, $m)) return $m[1];
    return null;
}

function require_api_token() {
    $token = api_bearer_token();
    if (!$token) { http_response_code(401); echo json_encode(['error'=>'no_token']); exit; }
    $pdo = get_pdo();
    $stmt = $pdo->prepare("SELECT id FROM users WHERE api_token = ? LIMIT 1");
    $stmt->execute([$token]);
    $u = $stmt->fetch();
    if (!$u) { http_response_code(401); echo json_encode(['error'=>'invalid_token']); exit; }
    return $u['id'];
}

function generate_token($length = 64) {
    return bin2hex(random_bytes($length/2));
}

/**
 * send_mail_smtp - tenta carregar PHPMailer manualmente (lib/phpmailer/src/)
 * Se os ficheiros forem encontrados, usa PHPMailer\PHPMailer\PHPMailer.
 * Caso contrário, faz fallback para mail().
 *
 * Retorna true se email foi enviado com sucesso, false caso contrário.
 */
function send_mail_smtp($to, $subject, $body) {
    // caminho onde esperamos encontrar PHPMailer manualmente instalado
    $phpmailer_src = __DIR__ . '/phpmailer/src';
    $phpmailer_files = [
        $phpmailer_src . '/PHPMailer.php',
        $phpmailer_src . '/SMTP.php',
        $phpmailer_src . '/Exception.php'
    ];

    $have_phpmailer = true;
    foreach ($phpmailer_files as $f) if (!file_exists($f)) { $have_phpmailer = false; break; }

    if ($have_phpmailer) {
        // carrega manualmente os ficheiros e usa o namespace PHPMailer\PHPMailer
        require_once $phpmailer_files[2]; // Exception.php
        require_once $phpmailer_files[1]; // SMTP.php
        require_once $phpmailer_files[0]; // PHPMailer.php

        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            // configurações SMTP
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            if (!empty(SMTP_SECURE)) $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port = SMTP_PORT;
            // opcional: definir timeout
            $mail->Timeout = 30;
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME ?? 'Txeka');
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = strip_tags($body);
            $mail->isHTML(false);

            return $mail->send();
        } catch (Exception $e) {
            error_log("PHPMailer Exception: " . $e->getMessage());
            // segue para fallback mail()
        } catch (Throwable $t) {
            error_log("PHPMailer Throwable: " . $t->getMessage());
        }
    } else {
        error_log("PHPMailer não encontrado em lib/phpmailer/src - usando mail()");
    }

    // fallback para mail()
    $headers = "From: " . MAIL_FROM . "\r\n" .
               "Reply-To: " . MAIL_FROM . "\r\n" .
               "MIME-Version: 1.0\r\n" .
               "Content-Type: text/plain; charset=UTF-8\r\n";
    return mail($to, $subject, $body, $headers);
}

function ensure_upload_dir() {
    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
}

function safe_filename($name) {
    $name = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $name);
    return $name;
}


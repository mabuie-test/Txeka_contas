<?php
// config.php - ajustar antes de usar
define('DB_HOST', 'localhost');
define('DB_NAME', 'txeka');
define('DB_USER', 'db_user');
define('DB_PASS', 'db_pass');

define('BASE_URL', 'https://teu-dominio.tld'); // sem barra final
define('UPLOAD_DIR', __DIR__ . '/uploads');     // pasta de uploads (gravável)
define('UPLOAD_URL', BASE_URL . '/uploads');    // url pública para os ficheiros

// SMTP (PHPMailer)
define('SMTP_HOST', 'smtp.exemplo.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'smtp_user@exemplo.com');
define('SMTP_PASS', 'senha_smtp');
define('SMTP_SECURE', 'tls'); // 'tls' ou 'ssl' ou '' para none
define('MAIL_FROM', 'no-reply@teu-dominio.tld');
define('MAIL_FROM_NAME', 'Txeka Recuperação');
define('MAIL_SUBJECT_RECOVER', 'Txeka — Recuperação de password');

// Uploads limits
define('UPLOAD_MAX_FILESIZE', 5 * 1024 * 1024); // 5 MB
define('UPLOAD_ALLOWED_MIMES', serialize(['image/jpeg','image/jpg','image/png']));

// Timezone
date_default_timezone_set('Africa/Maputo');


<?php
// api/upload.php
require_once __DIR__ . '/../lib/functions.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error'=>'method']); exit;
}

$userId = require_api_token(); // autentica

if (empty($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['error'=>'no_file']);
    exit;
}

$file = $_FILES['file'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error'=>'upload_error', 'code'=>$file['error']]);
    exit;
}

if ($file['size'] > UPLOAD_MAX_FILESIZE) {
    http_response_code(400);
    echo json_encode(['error'=>'too_large']);
    exit;
}

$allowed_mimes = unserialize(UPLOAD_ALLOWED_MIMES);
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);
if (!in_array($mime, $allowed_mimes)) {
    http_response_code(400);
    echo json_encode(['error'=>'invalid_type','mime'=>$mime]);
    exit;
}

ensure_upload_dir();
$userDir = rtrim(UPLOAD_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'user_' . intval($userId);
if (!is_dir($userDir)) mkdir($userDir, 0755, true);

$ext = ($mime === 'image/png') ? '.png' : '.jpg';
$filename = 'img_' . time() . '_' . bin2hex(random_bytes(6)) . $ext;
$filename = safe_filename($filename);
$destPath = $userDir . DIRECTORY_SEPARATOR . $filename;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    http_response_code(500);
    echo json_encode(['error'=>'save_failed']);
    exit;
}

$relativePath = 'user_' . intval($userId) . '/' . $filename;
$fileUrl = rtrim(UPLOAD_URL, '/') . '/' . $relativePath;

echo json_encode(['ok'=>true, 'url'=>$fileUrl]);
exit;


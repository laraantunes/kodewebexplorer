<?php
// api/serve.php - Servidor de mídias e documentos protegidos para visualização em modal
require_once __DIR__ . '/base.php';

$path = $_GET['path'] ?? '';
$absPath = get_absolute_path($path);

if (!file_exists($absPath) || is_dir($absPath)) {
    http_response_code(404);
    die("Arquivo não localizado.");
}

$ext = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));
$mimes = [
    'png'  => 'image/png',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif'  => 'image/gif',
    'svg'  => 'image/svg+xml',
    'webp' => 'image/webp',
    'ico'  => 'image/x-icon',
    'pdf'  => 'application/pdf',
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xls'  => 'application/vnd.ms-excel',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'csv'  => 'text/csv',
    'mp4'  => 'video/mp4',
    'mp3'  => 'audio/mpeg'
];

$mimeType = $mimes[$ext] ?? @mime_content_type($absPath) ?? 'application/octet-stream';

header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($absPath));
header('Cache-Control: private, max-age=3600');

readfile($absPath);
exit;

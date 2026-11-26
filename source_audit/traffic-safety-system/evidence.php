<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) { http_response_code(404); exit('Not found'); }
$statement = db()->prepare('SELECT evidence_path FROM reports WHERE id = ? LIMIT 1');
$statement->execute([$id]); $report = $statement->fetch();
if (!$report || empty($report['evidence_path'])) { http_response_code(404); exit('Evidence not found'); }
if (str_starts_with((string)$report['evidence_path'], 'tmp/traffic_safety_evidence/')) {
    $file = realpath(sys_get_temp_dir() . '/traffic_safety_evidence/' . basename($report['evidence_path']));
    $base = realpath(sys_get_temp_dir() . '/traffic_safety_evidence');
} else {
    $base = realpath(__DIR__ . '/storage/evidence');
    $file = realpath(__DIR__ . '/' . $report['evidence_path']);
}
if (!$base || !$file || !str_starts_with($file, $base . DIRECTORY_SEPARATOR) || !is_file($file)) { http_response_code(404); exit('Evidence not found'); }
$finfo = new finfo(FILEINFO_MIME_TYPE); $mime = $finfo->file($file);
if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) { http_response_code(415); exit('Unsupported evidence type'); }
header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($file));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
readfile($file);
exit;

<?php

require_once 'env.php';

$filename = $_ENV['REMOTE_FILE'] ?? null;

if (!$filename) {
    http_response_code(500);
    die('O nome do arquivo não está configurado no .env.');
}

// Caminho fixo da pasta + nome do arquivo
$file = __DIR__ . '/private_files/' . $filename;

// Verificar se existe
if (!file_exists($file)) {
    http_response_code(404);
    die('Arquivo não encontrado.');
}

// Cabeçalhos de download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($file));
header('Cache-Control: no-cache, must-revalidate');
header('Expires: 0');

readfile($file);
exit;

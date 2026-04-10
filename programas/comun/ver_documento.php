<?php
session_start();

$archivo = isset($_GET['f']) ? basename((string)$_GET['f']) : '';
if ($archivo === '') {
    http_response_code(400);
    echo 'Archivo invalido';
    exit;
}

// Ruta actual correcta y ruta legacy usada por versiones previas.
$ruta_publica = __DIR__ . '/../../documentos/' . $archivo;
$ruta_legacy = __DIR__ . '/../documentos/' . $archivo;

$ruta = null;
if (is_file($ruta_publica)) {
    $ruta = $ruta_publica;
} elseif (is_file($ruta_legacy)) {
    $ruta = $ruta_legacy;
}

if ($ruta === null) {
    http_response_code(404);
    echo 'Documento no encontrado';
    exit;
}

$ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
$mime = 'application/octet-stream';
if ($ext === 'pdf') {
    $mime = 'application/pdf';
} elseif ($ext === 'doc') {
    $mime = 'application/msword';
} elseif ($ext === 'docx') {
    $mime = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
} elseif ($ext === 'jpg' || $ext === 'jpeg') {
    $mime = 'image/jpeg';
} elseif ($ext === 'png') {
    $mime = 'image/png';
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($ruta));
header('Content-Disposition: inline; filename="' . rawurlencode($archivo) . '"');
readfile($ruta);
exit;

<?php
$query = $_SERVER['QUERY_STRING'] ?? '';
$destino = 'documentos.html';
if($query !== ''){
    $destino .= '?' . $query;
}
header('Location: ' . $destino);
exit;
?>

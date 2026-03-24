<?php
include("conexion.php");
header("Content-Type: application/json; charset=utf-8");

$torre = isset($_GET['torre']) ? trim($_GET['torre']) : '';

$sql = "SELECT i.id_inmueble, i.numero, t.nombre AS torre
        FROM INMUEBLES i
        INNER JOIN TORRES t ON t.id_torre = i.id_torre";

if($torre !== ''){
    $torre_sql = mysqli_real_escape_string($conexion, $torre);
    $sql .= " WHERE t.nombre = '$torre_sql'";
}

$sql .= " ORDER BY t.nombre, i.numero";

$res = mysqli_query($conexion, $sql);
$inmuebles = [];

if($res){
    while($fila = mysqli_fetch_assoc($res)){
        $inmuebles[] = [
            'id_inmueble' => intval($fila['id_inmueble']),
            'numero' => $fila['numero'],
            'torre' => $fila['torre']
        ];
    }
}

echo json_encode(['ok' => true, 'inmuebles' => $inmuebles]);

<?php
include("conexion.php");
header("Content-Type: application/json; charset=utf-8");

$torres = [];
$sql = "SELECT id_torre, nombre FROM TORRES ORDER BY nombre";
$res = mysqli_query($conexion, $sql);

if($res){
    while($fila = mysqli_fetch_assoc($res)){
        $torres[] = [
            'id_torre' => intval($fila['id_torre']),
            'nombre' => $fila['nombre']
        ];
    }
}

echo json_encode(['ok' => true, 'torres' => $torres]);

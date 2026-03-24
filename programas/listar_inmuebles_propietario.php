<?php
session_start();
include("conexion.php");

header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION['rol']) || intval($_SESSION['rol']) !== 4 || !isset($_SESSION['id_propietario'])) {
    echo json_encode(["ok" => false, "error" => "No autorizado", "inmuebles" => []]);
    exit;
}

$id_propietario = intval($_SESSION['id_propietario']);

$sql = "SELECT i.id_inmueble, i.numero, t.nombre AS torre
        FROM INMUEBLES i
        INNER JOIN TORRES t ON i.id_torre = t.id_torre
        WHERE i.id_propietario = $id_propietario
        ORDER BY t.nombre, i.numero";

$res = mysqli_query($conexion, $sql);
$inmuebles = [];

if ($res) {
    while ($fila = mysqli_fetch_assoc($res)) {
        $inmuebles[] = [
            "id_inmueble" => intval($fila["id_inmueble"]),
            "numero" => $fila["numero"],
            "torre" => $fila["torre"]
        ];
    }
}

echo json_encode(["ok" => true, "inmuebles" => $inmuebles]);

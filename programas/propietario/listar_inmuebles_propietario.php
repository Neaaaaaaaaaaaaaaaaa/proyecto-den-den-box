<?php
session_start();
include("../comun/conexion.php");

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['rol']) || intval($_SESSION['rol']) !== 4) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'No autorizado']);
    exit;
}

$id_propietario = intval($_SESSION['id_propietario'] ?? 0);
if ($id_propietario <= 0 && isset($_SESSION['id_persona'])) {
    $id_persona = intval($_SESSION['id_persona']);
    $q_prop = mysqli_query($conexion, "SELECT id_propietario FROM PROPIETARIOS WHERE id_persona=$id_persona LIMIT 1");
    if ($q_prop && mysqli_num_rows($q_prop) > 0) {
        $id_propietario = intval(mysqli_fetch_assoc($q_prop)['id_propietario']);
        $_SESSION['id_propietario'] = $id_propietario;
    }
}

if ($id_propietario <= 0) {
    echo json_encode(['ok' => true, 'items' => []]);
    exit;
}

$sql = "SELECT i.id_inmueble, i.numero, COALESCE(t.nombre, 'Sin torre') AS torre
        FROM INMUEBLES i
        LEFT JOIN TORRES t ON t.id_torre = i.id_torre
        WHERE i.id_propietario = $id_propietario
        ORDER BY t.nombre, i.numero";

$res = mysqli_query($conexion, $sql);
$items = [];

if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $items[] = [
            'id_inmueble' => intval($row['id_inmueble']),
            'numero' => (string) $row['numero'],
            'torre' => (string) $row['torre']
        ];
    }
}

echo json_encode(['ok' => true, 'items' => $items]);

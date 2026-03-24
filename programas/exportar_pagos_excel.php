<?php
session_start();
include("conexion.php");

$rol = $_SESSION['rol'] ?? null;
if ($rol === null) {
    header('HTTP/1.1 403 Forbidden');
    echo "Acceso denegado";
    exit;
}

$inicio = trim($_GET['inicio'] ?? '');
$fin = trim($_GET['fin'] ?? '');
$torre = trim($_GET['torre'] ?? '');
$id_inmueble = isset($_GET['id_inmueble']) ? intval($_GET['id_inmueble']) : 0;

$validDate = function($d){
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) !== false;
};
if ($inicio !== '' && !$validDate($inicio)) $inicio = '';
if ($fin !== '' && !$validDate($fin)) $fin = '';
if ($inicio !== '' && $fin !== '' && $inicio > $fin) {
    [$inicio, $fin] = [$fin, $inicio];
}


$condiciones = [];
if ($inicio !== '' && $fin !== '') {
    $condiciones[] = "p.fecha_pago BETWEEN '$inicio' AND '$fin'";
} elseif ($inicio !== '') {
    $condiciones[] = "p.fecha_pago >= '$inicio'";
} elseif ($fin !== '') {
    $condiciones[] = "p.fecha_pago <= '$fin'";
}
if ($id_inmueble > 0) {
    $condiciones[] = "p.id_inmueble = $id_inmueble";
}
if ($torre !== '') {
    $torre_sql = mysqli_real_escape_string($conexion, $torre);
    $condiciones[] = "EXISTS (
        SELECT 1
        FROM INMUEBLES ii
        INNER JOIN TORRES tt ON tt.id_torre = ii.id_torre
        WHERE ii.id_inmueble = p.id_inmueble AND tt.nombre = '$torre_sql'
    )";
}
$condicion_sql = implode(' AND ', $condiciones);
$filtro_pago = ($condicion_sql !== '') ? ('WHERE ' . $condicion_sql) : '';

$sql_kpi_total = "SELECT COUNT(*) total FROM PAGOS p $filtro_pago";
$total_pagos = mysqli_fetch_assoc(mysqli_query($conexion, $sql_kpi_total))['total'] ?? 0;

$sql_kpi_pagado = "SELECT COALESCE(SUM(p.valor),0) total FROM PAGOS p WHERE p.estado_pago='Pagado'";
if ($condicion_sql !== '') $sql_kpi_pagado .= ' AND ' . $condicion_sql;
$pagado = mysqli_fetch_assoc(mysqli_query($conexion, $sql_kpi_pagado))['total'] ?? 0;

$sql_kpi_pendiente = "SELECT COALESCE(SUM(p.valor),0) total FROM PAGOS p WHERE p.estado_pago='Pendiente'";
if ($condicion_sql !== '') $sql_kpi_pendiente .= ' AND ' . $condicion_sql;
$pendiente = mysqli_fetch_assoc(mysqli_query($conexion, $sql_kpi_pendiente))['total'] ?? 0;

$sql_detalle = "SELECT p.id_pago, p.fecha_pago, p.valor, p.estado_pago, p.metodo_pago,
                       i.id_inmueble, i.numero AS apartamento, t.nombre AS torre
                FROM PAGOS p
                LEFT JOIN INMUEBLES i ON i.id_inmueble = p.id_inmueble
                LEFT JOIN TORRES t ON t.id_torre = i.id_torre
                $filtro_pago
                ORDER BY p.fecha_pago DESC, p.id_pago DESC";
$res_detalle = mysqli_query($conexion, $sql_detalle);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=reportes_pagos_' . date('Ymd_His') . '.csv');

$out = fopen('php://output', 'w');
fputcsv($out, ['Reporte de Pagos - Den Den Box']);
fputcsv($out, ['Generado', date('Y-m-d H:i:s')]);
fputcsv($out, ['Filtro inicio', $inicio ?: 'N/A']);
fputcsv($out, ['Filtro fin', $fin ?: 'N/A']);
fputcsv($out, ['Filtro torre', $torre ?: 'N/A']);
fputcsv($out, ['Filtro inmueble', $id_inmueble > 0 ? $id_inmueble : 'N/A']);
fputcsv($out, []);
fputcsv($out, ['KPI', 'Valor']);
fputcsv($out, ['Total de pagos', $total_pagos]);
fputcsv($out, ['Total recaudado (Pagado)', $pagado]);
fputcsv($out, ['Saldo pendiente (Pendiente)', $pendiente]);
fputcsv($out, []);
fputcsv($out, ['ID Pago', 'Fecha', 'Valor', 'Estado', 'Metodo', 'ID Inmueble', 'Torre', 'Apartamento']);

if ($res_detalle) {
    while ($row = mysqli_fetch_assoc($res_detalle)) {
        fputcsv($out, [
            $row['id_pago'],
            $row['fecha_pago'],
            $row['valor'],
            $row['estado_pago'],
            $row['metodo_pago'],
            $row['id_inmueble'],
            $row['torre'],
            $row['apartamento']
        ]);
    }
}

fclose($out);
exit;

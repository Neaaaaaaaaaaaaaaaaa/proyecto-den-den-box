<?php
session_start();
include("conexion.php");

// Solo usuarios autenticados pueden exportar
$rol = $_SESSION['rol'] ?? null;
if ($rol === null) {
    header('HTTP/1.1 403 Forbidden');
    echo "Acceso denegado";
    exit;
}

// Incluir la lógica de reporte_avanzado.php para obtener los datos
// Copiar el código de filtros y cálculos

/* ===== FILTRO FECHAS ===== */
$inicio = trim($_GET['inicio'] ?? '');
$fin = trim($_GET['fin'] ?? '');

$validDate = function($d){
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) !== false;
};

if ($inicio !== '' && !$validDate($inicio)) {
    $inicio = '';
}
if ($fin !== '' && !$validDate($fin)) {
    $fin = '';
}

if ($inicio !== '' && $fin !== '' && $inicio > $fin) {
    [$inicio, $fin] = [$fin, $inicio];
}

$filtro_novedad = "";
$filtro_pago = "";

if ($inicio !== '' && $fin !== '') {
    $filtro_novedad = "WHERE fecha_reporte BETWEEN '$inicio' AND '$fin'";
    $filtro_pago = "WHERE fecha_pago BETWEEN '$inicio' AND '$fin'";
} elseif ($inicio !== '') {
    $filtro_novedad = "WHERE fecha_reporte >= '$inicio'";
    $filtro_pago = "WHERE fecha_pago >= '$inicio'";
} elseif ($fin !== '') {
    $filtro_novedad = "WHERE fecha_reporte <= '$fin'";
    $filtro_pago = "WHERE fecha_pago <= '$fin'";
}

/* ===== KPI OCUPACION ===== */
$total_inmuebles = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) total FROM INMUEBLES"))['total'] ?? 0;
$ocupados = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(DISTINCT id_inmueble) total FROM RESIDENTE_INMUEBLE"))['total'];
$ocupacion = ($total_inmuebles > 0) ? round(($ocupados / $total_inmuebles) * 100, 2) : 0;

/* ===== KPI MOROSIDAD ===== */
$total_pagos = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) total FROM PAGOS $filtro_pago"))['total'] ?? 0;
$moros = "SELECT COUNT(*) total FROM PAGOS WHERE estado_pago='Pendiente'";
if ($filtro_pago !== '') {
    $moros .= ' AND ' . str_replace('WHERE ', '', $filtro_pago);
}
$morosos = mysqli_fetch_assoc(mysqli_query($conexion, $moros))['total'] ?? 0;
$morosidad = ($total_pagos > 0) ? round(($morosos / $total_pagos) * 100, 2) : 0;

/* ===== KPI NOVEDADES ===== */
$total_novedades = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) total FROM NOVEDAD $filtro_novedad"))['total'];

/* ===== KPI USUARIOS ===== */
$total_propietarios = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) total FROM PROPIETARIOS"))['total'] ?? 0;
$total_residentes = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) total FROM RESIDENTES"))['total'] ?? 0;
$total_usuarios = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) total FROM USUARIOS"))['total'] ?? 0;

/* ===== KPI COMUNICACIONES/DOCUMENTOS ===== */
$total_comunicaciones = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) total FROM COMUNICACIONES"))['total'] ?? 0;
$total_documentos = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) total FROM DOCUMENTOS"))['total'] ?? 0;

/* ===== KPI PAGOS ===== */
$pagos_recaudados_query = "SELECT COALESCE(SUM(valor),0) total FROM PAGOS WHERE estado_pago='Pagado'";
if ($filtro_pago !== '') {
    $pagos_recaudados_query .= ' AND ' . str_replace('WHERE ', '', $filtro_pago);
}
$pagos_recaudados = mysqli_fetch_assoc(mysqli_query($conexion, $pagos_recaudados_query))['total'] ?? 0;

$pagos_pendientes_query = "SELECT COUNT(*) total FROM PAGOS WHERE estado_pago='Pendiente'";
if ($filtro_pago !== '') {
    $pagos_pendientes_query .= ' AND ' . str_replace('WHERE ', '', $filtro_pago);
}
$pagos_pendientes = mysqli_fetch_assoc(mysqli_query($conexion, $pagos_pendientes_query))['total'] ?? 0;

$pagos_total = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) total FROM PAGOS $filtro_pago"))['total'] ?? 0;

// Para Excel, output CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=reporte_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');

// Encabezados
fputcsv($output, ['Métrica', 'Valor']);

// Datos
fputcsv($output, ['Ocupación (%)', $ocupacion]);
fputcsv($output, ['Morosidad (%)', $morosidad]);
fputcsv($output, ['Total Novedades', $total_novedades]);
fputcsv($output, ['Total Propietarios', $total_propietarios]);
fputcsv($output, ['Total Residentes', $total_residentes]);
fputcsv($output, ['Total Usuarios', $total_usuarios]);
fputcsv($output, ['Total Comunicaciones', $total_comunicaciones]);
fputcsv($output, ['Total Documentos', $total_documentos]);
fputcsv($output, ['Pagos Recaudados', $pagos_recaudados]);
fputcsv($output, ['Pagos Pendientes', $pagos_pendientes]);
fputcsv($output, ['Total Pagos', $pagos_total]);

fclose($output);
?>
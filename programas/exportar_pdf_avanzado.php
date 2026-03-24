<?php
session_start();
include("conexion.php");

ini_set('display_errors', '0');

// Solo usuarios autenticados pueden exportar reportes
$rol = $_SESSION['rol'] ?? null;
if ($rol === null) {
    echo "<div class='container'><h1>Acceso denegado</h1><p>Inicie sesión para exportar reportes.</p><a href='../html/login.html' class='buttonplace'>Ir a login</a></div>";
    exit;
}

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

$filtro_pago = '';
$filtro_novedad = '';
if ($inicio !== '' && $fin !== '') {
    $filtro_pago = "WHERE fecha_pago BETWEEN '$inicio' AND '$fin'";
    $filtro_novedad = "WHERE fecha_reporte BETWEEN '$inicio' AND '$fin'";
} elseif ($inicio !== '') {
    $filtro_pago = "WHERE fecha_pago >= '$inicio'";
    $filtro_novedad = "WHERE fecha_reporte >= '$inicio'";
} elseif ($fin !== '') {
    $filtro_pago = "WHERE fecha_pago <= '$fin'";
    $filtro_novedad = "WHERE fecha_reporte <= '$fin'";
}

// Datos usados en reporte
$total_inmuebles = mysqli_fetch_assoc(mysqli_query($conexion,
"SELECT COUNT(*) total FROM INMUEBLES"))['total'] ?? 0;
$total_propietarios = mysqli_fetch_assoc(mysqli_query($conexion,
"SELECT COUNT(*) total FROM PROPIETARIOS"))['total'] ?? 0;
$total_residentes = mysqli_fetch_assoc(mysqli_query($conexion,
"SELECT COUNT(*) total FROM RESIDENTES"))['total'] ?? 0;
$total_usuarios = mysqli_fetch_assoc(mysqli_query($conexion,
"SELECT COUNT(*) total FROM USUARIOS"))['total'] ?? 0;
$total_comunicaciones = mysqli_fetch_assoc(mysqli_query($conexion,
"SELECT COUNT(*) total FROM COMUNICACIONES"))['total'] ?? 0;
$total_documentos = mysqli_fetch_assoc(mysqli_query($conexion,
"SELECT COUNT(*) total FROM DOCUMENTOS"))['total'] ?? 0;

$torres = mysqli_query($conexion,"SELECT t.nombre, COUNT(i.id_inmueble) total FROM TORRES t LEFT JOIN INMUEBLES i ON t.id_torre=i.id_torre GROUP BY t.nombre");

$tendencia = mysqli_query($conexion,"SELECT MONTH(fecha_reporte) mes, COUNT(*) total FROM NOVEDAD $filtro_novedad GROUP BY MONTH(fecha_reporte) ORDER BY mes");

$pagoFiltroQuery = "SELECT COUNT(*) total FROM PAGOS WHERE estado_pago='Pendiente'";
if ($filtro_pago !== '') {
    $pagoFiltroQuery .= ' AND ' . str_replace('WHERE ', '', $filtro_pago);
}
$pagos = mysqli_query($conexion, $pagoFiltroQuery);
$pagoData = mysqli_fetch_assoc($pagos);
$morosos = $pagoData['total'] ?? 0;

$pagos_recaudados_query = "SELECT COALESCE(SUM(valor),0) total FROM PAGOS WHERE estado_pago='Pagado'";
if ($filtro_pago !== '') {
    $pagos_recaudados_query .= ' AND ' . str_replace('WHERE ', '', $filtro_pago);
}
$pagos_recaudados = mysqli_fetch_assoc(mysqli_query($conexion, $pagos_recaudados_query))['total'] ?? 0;
$pagos_total = mysqli_fetch_assoc(mysqli_query($conexion,
"SELECT COUNT(*) total FROM PAGOS $filtro_pago"))['total'] ?? 0;

$pagos_estadistica = mysqli_fetch_assoc(mysqli_query($conexion,
"SELECT
    COUNT(*) as cnt,
    COALESCE(AVG(valor),0) as promedio,
    COALESCE(STDDEV_POP(valor),0) as stddev,
    COALESCE(MIN(valor),0) as min_valor,
    COALESCE(MAX(valor),0) as max_valor,
    COALESCE(SUM(valor),0) as suma_valor
FROM PAGOS $filtro_pago" ));

$pagos_cnt = $pagos_estadistica['cnt'] ?? 0;
$pagos_promedio = round($pagos_estadistica['promedio'] ?? 0, 2);
$pagos_stddev = round($pagos_estadistica['stddev'] ?? 0, 2);
$pagos_min = $pagos_estadistica['min_valor'] ?? 0;
$pagos_max = $pagos_estadistica['max_valor'] ?? 0;
$pagos_suma = $pagos_estadistica['suma_valor'] ?? 0;

function pdf_txt($text) {
    $s = (string)$text;
    $c = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $s);
    return ($c !== false) ? $c : $s;
}

// Fallback a CSV si faltan librerías FPDF
$fpdfPath = __DIR__ . '/../fpdf/fpdf.php';
if (!file_exists($fpdfPath)) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="reporte_administrativo_' . date('Ymd_His') . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Reporte Administrativo - Den Den Box']);
    fputcsv($output, []);
    fputcsv($output, ['Fecha generación', date('Y-m-d H:i:s')]);
    fputcsv($output, ['Filtro inicio', $inicio ?: 'N/A']);
    fputcsv($output, ['Filtro fin', $fin ?: 'N/A']);
    fputcsv($output, []);
    fputcsv($output, ['KPI', 'Valor']);
    fputcsv($output, ['Total Inmuebles', $total_inmuebles]);
    fputcsv($output, ['Propietarios', $total_propietarios]);
    fputcsv($output, ['Residentes', $total_residentes]);
    fputcsv($output, ['Usuarios', $total_usuarios]);
    fputcsv($output, ['Comunicaciones', $total_comunicaciones]);
    fputcsv($output, ['Documentos', $total_documentos]);
    fputcsv($output, ['Pagos pendientes', $morosos]);
    fputcsv($output, ['Pagos recaudados', $pagos_recaudados]);
    fputcsv($output, ['Total pagos', $pagos_total]);
    fputcsv($output, ['Pagos registros', $pagos_cnt]);
    fputcsv($output, ['Pago promedio', $pagos_promedio]);
    fputcsv($output, ['Pago stddev', $pagos_stddev]);
    fputcsv($output, ['Pago mínimo', $pagos_min]);
    fputcsv($output, ['Pago máximo', $pagos_max]);
    fputcsv($output, ['Pago suma', $pagos_suma]);
    fputcsv($output, []);
    fputcsv($output, ['Ocupacion por Torre', 'Total Inmuebles']);
    while ($t = mysqli_fetch_assoc($torres)) {
        fputcsv($output, [$t['nombre'], $t['total']]);
    }
    fputcsv($output, []);
    fputcsv($output, ['Tendencia de Novedades (Mes)', 'Total']);
    while ($row = mysqli_fetch_assoc($tendencia)) {
        fputcsv($output, [$row['mes'], $row['total']]);
    }
    fclose($output);
    exit;
}

require($fpdfPath);

if (ob_get_length()) {
    ob_clean();
}

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,pdf_txt('Reporte Administrativo - Den Den Box'),0,1,'C');
$pdf->SetFont('Arial','',12);
$pdf->Cell(0,10,pdf_txt('Fecha generacion: ' . date('Y-m-d H:i:s')),0,1);
$pdf->Cell(0,8,pdf_txt('Filtro inicio: ' . ($inicio ?: 'N/A')),0,1);
$pdf->Cell(0,8,pdf_txt('Filtro fin: ' . ($fin ?: 'N/A')),0,1);
$pdf->Ln(4);
$pdf->Cell(0,10,pdf_txt("Total Inmuebles: $total_inmuebles"),0,1);
$pdf->Cell(0,8,pdf_txt("Propietarios: $total_propietarios"),0,1);
$pdf->Cell(0,8,pdf_txt("Residentes: $total_residentes"),0,1);
$pdf->Cell(0,8,pdf_txt("Usuarios: $total_usuarios"),0,1);
$pdf->Cell(0,8,pdf_txt("Comunicaciones: $total_comunicaciones"),0,1);
$pdf->Cell(0,8,pdf_txt("Documentos: $total_documentos"),0,1);
$pdf->Cell(0,8,pdf_txt("Pagos pendientes: $morosos"),0,1);
$pdf->Cell(0,8,pdf_txt("Pagos recaudados: $pagos_recaudados"),0,1);
$pdf->Cell(0,8,pdf_txt("Total pagos: $pagos_total"),0,1);
$pdf->Cell(0,8,pdf_txt("Pagos registros: $pagos_cnt"),0,1);
$pdf->Cell(0,8,pdf_txt("Pago promedio: $pagos_promedio"),0,1);
$pdf->Cell(0,8,pdf_txt("Pago stddev: $pagos_stddev"),0,1);
$pdf->Cell(0,8,pdf_txt("Pago minimo: $pagos_min"),0,1);
$pdf->Cell(0,8,pdf_txt("Pago maximo: $pagos_max"),0,1);
$pdf->Cell(0,8,pdf_txt("Pago suma: $pagos_suma"),0,1);
$pdf->Ln(6);
$pdf->Cell(0,10,pdf_txt('Ocupacion por Torre:'),0,1);
foreach (mysqli_fetch_all($torres, MYSQLI_ASSOC) as $t) {
    $pdf->Cell(0,8,pdf_txt($t['nombre'] . ' - ' . $t['total']),0,1);
}
$pdf->Ln(6);
$pdf->Cell(0,10,pdf_txt('Novedades por Mes:'),0,1);
foreach (mysqli_fetch_all($tendencia, MYSQLI_ASSOC) as $row) {
    $pdf->Cell(0,8,pdf_txt('Mes ' . $row['mes'] . ' - ' . $row['total']),0,1);
}
$pdf->Output('D', 'reporte_administrativo_' . date('Ymd_His') . '.pdf');

?>
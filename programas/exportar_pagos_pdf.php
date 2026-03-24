<?php
session_start();
include("conexion.php");

ini_set('display_errors', '0');

$rol = $_SESSION['rol'] ?? null;
if ($rol === null) {
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

$total_pagos = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) total FROM PAGOS p $filtro_pago"))['total'] ?? 0;

$sql_pagado = "SELECT COALESCE(SUM(p.valor),0) total FROM PAGOS p WHERE p.estado_pago='Pagado'";
if ($condicion_sql !== '') $sql_pagado .= ' AND ' . $condicion_sql;
$pagado = mysqli_fetch_assoc(mysqli_query($conexion, $sql_pagado))['total'] ?? 0;

$sql_pendiente = "SELECT COALESCE(SUM(p.valor),0) total FROM PAGOS p WHERE p.estado_pago='Pendiente'";
if ($condicion_sql !== '') $sql_pendiente .= ' AND ' . $condicion_sql;
$pendiente = mysqli_fetch_assoc(mysqli_query($conexion, $sql_pendiente))['total'] ?? 0;

$sql_detalle = "SELECT p.id_pago, p.fecha_pago, p.valor, p.estado_pago,
                       i.numero AS apartamento, t.nombre AS torre
                FROM PAGOS p
                LEFT JOIN INMUEBLES i ON i.id_inmueble = p.id_inmueble
                LEFT JOIN TORRES t ON t.id_torre = i.id_torre
                $filtro_pago
                ORDER BY p.fecha_pago DESC, p.id_pago DESC
                LIMIT 200";
$res_detalle = mysqli_query($conexion, $sql_detalle);

function pdf_txt($text) {
    $s = (string)$text;
    $c = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $s);
    return ($c !== false) ? $c : $s;
}

$fpdfPath = __DIR__ . '/../fpdf/fpdf.php';
if (!file_exists($fpdfPath)) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=reportes_pagos_' . date('Ymd_His') . '.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Reporte de Pagos - Den Den Box']);
    fputcsv($out, ['Total pagos', $total_pagos]);
    fputcsv($out, ['Total pagado', $pagado]);
    fputcsv($out, ['Total pendiente', $pendiente]);
    fputcsv($out, []);
    fputcsv($out, ['ID', 'Fecha', 'Valor', 'Estado', 'Torre', 'Apto']);
    if ($res_detalle) {
        while ($r = mysqli_fetch_assoc($res_detalle)) {
            fputcsv($out, [$r['id_pago'], $r['fecha_pago'], $r['valor'], $r['estado_pago'], $r['torre'], $r['apartamento']]);
        }
    }
    fclose($out);
    exit;
}

require($fpdfPath);

if (ob_get_length()) {
    ob_clean();
}

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,pdf_txt('Reporte de Pagos - Den Den Box'),0,1,'C');
$pdf->SetFont('Arial','',10);
$pdf->Cell(0,6,pdf_txt('Generado: '.date('Y-m-d H:i:s')),0,1);
$pdf->Cell(0,6,pdf_txt('Filtro inicio: '.($inicio ?: 'N/A')),0,1);
$pdf->Cell(0,6,pdf_txt('Filtro fin: '.($fin ?: 'N/A')),0,1);
$pdf->Cell(0,6,pdf_txt('Filtro torre: '.($torre ?: 'N/A')),0,1);
$pdf->Cell(0,6,pdf_txt('Filtro inmueble: '.($id_inmueble > 0 ? $id_inmueble : 'N/A')),0,1);
$pdf->Ln(2);
$pdf->Cell(0,6,pdf_txt('Total pagos: '.$total_pagos),0,1);
$pdf->Cell(0,6,pdf_txt('Total pagado: '.number_format($pagado,2)),0,1);
$pdf->Cell(0,6,pdf_txt('Total pendiente: '.number_format($pendiente,2)),0,1);
$pdf->Ln(4);

$pdf->SetFont('Arial','B',9);
$pdf->Cell(16,7,'ID',1);
$pdf->Cell(28,7,'Fecha',1);
$pdf->Cell(28,7,'Valor',1);
$pdf->Cell(28,7,'Estado',1);
$pdf->Cell(35,7,'Torre',1);
$pdf->Cell(35,7,'Apto',1);
$pdf->Ln();

$pdf->SetFont('Arial','',9);
if ($res_detalle) {
    while ($r = mysqli_fetch_assoc($res_detalle)) {
        $pdf->Cell(16,7,pdf_txt($r['id_pago']),1);
        $pdf->Cell(28,7,pdf_txt($r['fecha_pago']),1);
        $pdf->Cell(28,7,pdf_txt(number_format($r['valor'],2)),1);
        $pdf->Cell(28,7,pdf_txt($r['estado_pago']),1);
        $pdf->Cell(35,7,pdf_txt(substr((string)$r['torre'],0,18)),1);
        $pdf->Cell(35,7,pdf_txt(substr((string)$r['apartamento'],0,18)),1);
        $pdf->Ln();
    }
}

$pdf->Output('D', 'reportes_pagos_' . date('Ymd_His') . '.pdf');
exit;

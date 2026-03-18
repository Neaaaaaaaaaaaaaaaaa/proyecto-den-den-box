<?php
require('../fpdf/fpdf.php');
include("conexion.php");

$pdf = new FPDF();
$pdf->AddPage();

$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Reporte Administrativo - Den Den Box',0,1,'C');

$pdf->SetFont('Arial','',12);

/* OCUPACION */
$total_inmuebles = mysqli_fetch_assoc(mysqli_query($conexion,
"SELECT COUNT(*) total FROM INMUEBLES"))['total'];

$pdf->Cell(0,10,"Total Inmuebles: $total_inmuebles",0,1);


/* TORRES */
$pdf->Cell(0,10,"Ocupacion por Torre:",0,1);

$torres = mysqli_query($conexion,"
SELECT t.nombre, COUNT(i.id_inmueble) total
FROM TORRES t
LEFT JOIN INMUEBLES i ON t.id_torre=i.id_torre
GROUP BY t.nombre");

while($t = mysqli_fetch_assoc($torres)){
    $pdf->Cell(0,8,$t['nombre']." - ".$t['total'],0,1);
}


/* NOVEDADES */
$pdf->Cell(0,10,"Novedades por Mes:",0,1);

$tendencia = mysqli_query($conexion,"
SELECT MONTH(fecha_reporte) mes, COUNT(*) total
FROM NOVEDAD
GROUP BY MONTH(fecha_reporte)");

while($row = mysqli_fetch_assoc($tendencia)){
    $pdf->Cell(0,8,"Mes ".$row['mes']." - ".$row['total'],0,1);
}


/* PAGOS */
$pdf->Cell(0,10,"Pagos Pendientes:",0,1);

$pagos = mysqli_query($conexion,"
SELECT COUNT(*) total FROM PAGOS WHERE estado_pago='Pendiente'");

$p = mysqli_fetch_assoc($pagos);

$pdf->Cell(0,8,"Morosos: ".$p['total'],0,1);

$pdf->Output();
?>
<?php
include("conexion.php");

/* ===== FILTRO FECHAS ===== */
$inicio = $_GET['inicio'] ?? '';
$fin = $_GET['fin'] ?? '';

$filtro_novedad = "";
$filtro_pago = "";

if($inicio && $fin){
    $filtro_novedad = "WHERE fecha_reporte BETWEEN '$inicio' AND '$fin'";
    $filtro_pago = "WHERE fecha_pago BETWEEN '$inicio' AND '$fin'";
}

/* ===== KPI OCUPACION ===== */
$total_inmuebles = mysqli_fetch_assoc(mysqli_query($conexion,
"SELECT COUNT(*) total FROM INMUEBLES"))['total'];

$ocupados = mysqli_fetch_assoc(mysqli_query($conexion,
"SELECT COUNT(DISTINCT id_inmueble) total FROM RESIDENTE_INMUEBLE"))['total'];

$ocupacion = ($total_inmuebles>0)? round(($ocupados/$total_inmuebles)*100,2):0;


/* ===== KPI MOROSIDAD ===== */
$total_pagos = mysqli_fetch_assoc(mysqli_query($conexion,
"SELECT COUNT(*) total FROM PAGOS $filtro_pago"))['total'];

$morosos = mysqli_fetch_assoc(mysqli_query($conexion,
"SELECT COUNT(*) total FROM PAGOS 
WHERE estado_pago='Pendiente'"))['total'];

$morosidad = ($total_pagos>0)? round(($morosos/$total_pagos)*100,2):0;


/* ===== KPI NOVEDADES (PQRS) ===== */
$total_novedades = mysqli_fetch_assoc(mysqli_query($conexion,
"SELECT COUNT(*) total FROM NOVEDAD $filtro_novedad"))['total'];


/* ===== KPI POR TORRE ===== */
$torres = mysqli_query($conexion,"
SELECT t.nombre,
COUNT(i.id_inmueble) total_inmuebles,
COUNT(DISTINCT ri.id_inmueble) ocupados
FROM TORRES t
LEFT JOIN INMUEBLES i ON t.id_torre=i.id_torre
LEFT JOIN RESIDENTE_INMUEBLE ri ON i.id_inmueble=ri.id_inmueble
GROUP BY t.nombre");


/* ===== TENDENCIA NOVEDADES ===== */
$tendencia = mysqli_query($conexion,"
SELECT MONTH(fecha_reporte) mes, COUNT(*) total
FROM NOVEDAD
GROUP BY MONTH(fecha_reporte)
ORDER BY mes");


?>

<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Reporte Profesional</title>
<link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="container">

<h1>Dashboard Administrativo</h1>

<!-- KPI -->
<div class="kpi-row">

<div class="card">
Ocupación<br><br>
<h2><?php echo $ocupacion; ?>%</h2>
</div>

<div class="card">
Morosidad<br><br>
<h2><?php echo $morosidad; ?>%</h2>
</div>

<div class="card">
Novedades (PQRS)<br><br>
<h2><?php echo $total_novedades; ?></h2>
</div>

</div>

<br>

<!-- KPI TORRES -->
<h2>Ocupación por Torre</h2>

<table class="residentes-table">
<tr>
<th>Torre</th>
<th>Total</th>
<th>Ocupados</th>
<th>% Ocupación</th>
</tr>

<?php while($t = mysqli_fetch_assoc($torres)){ 
$porcentaje = ($t['total_inmuebles']>0) 
? round(($t['ocupados']/$t['total_inmuebles'])*100,2) : 0;
?>

<tr>
<td><?php echo $t['nombre']; ?></td>
<td><?php echo $t['total_inmuebles']; ?></td>
<td><?php echo $t['ocupados']; ?></td>
<td><?php echo $porcentaje; ?>%</td>
</tr>

<?php } ?>

</table>

<br>

<!-- TENDENCIA -->
<h2>Tendencia de Novedades</h2>

<table class="residentes-table">
<tr><th>Mes</th><th>Total</th></tr>

<?php while($row = mysqli_fetch_assoc($tendencia)){ ?>
<tr>
<td><?php echo $row['mes']; ?></td>
<td><?php echo $row['total']; ?></td>
</tr>
<?php } ?>

</table>

<br>

<a href="exportar_pdf_avanzado.php" class="buttonplace">Exportar PDF</a>

<br><br>

<a href="../html/admin/reportes.html" class="buttonplace">Volver</a>

</div>

</body>
</html>
<?php
session_start();
include("conexion.php");

// Solo usuarios autenticados pueden ver reportes (admin/operator/propietario/residente)
$rol = $_SESSION['rol'] ?? null;
if ($rol === null) {
    echo "<div class='container'><h1>Acceso denegado</h1><p>Inicie sesión para ver este reporte.</p><a href='../html/login.html' class='buttonplace'>Ir a login</a></div>";
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
    // intercambiar si el intervalo está invertido
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
$total_inmuebles = mysqli_fetch_assoc(mysqli_query($conexion,
"SELECT COUNT(*) total FROM INMUEBLES"))['total'] ?? 0;

$ocupados = mysqli_fetch_assoc(mysqli_query($conexion,
"SELECT COUNT(DISTINCT id_inmueble) total FROM RESIDENTE_INMUEBLE"))['total'];

$ocupacion = ($total_inmuebles>0)? round(($ocupados/$total_inmuebles)*100,2):0;


/* ===== KPI MOROSIDAD ===== */
$total_pagos = mysqli_fetch_assoc(mysqli_query($conexion,
"SELECT COUNT(*) total FROM PAGOS $filtro_pago"))['total'] ?? 0;

$moros = "SELECT COUNT(*) total FROM PAGOS WHERE estado_pago='Pendiente'";
if ($filtro_pago !== '') {
    $moros .= ($filtro_pago[1] === 'W') ? ' AND' : ' WHERE';
    $moros .= str_replace('WHERE ', '', $filtro_pago);
}
$morosos = mysqli_fetch_assoc(mysqli_query($conexion, $moros))['total'] ?? 0;

$morosidad = ($total_pagos > 0) ? round(($morosos/$total_pagos)*100,2) : 0;


/* ===== KPI NOVEDADES (PQRS) ===== */
$total_novedades = mysqli_fetch_assoc(mysqli_query($conexion,
"SELECT COUNT(*) total FROM NOVEDAD $filtro_novedad"))['total'];

/* ===== KPI USUARIOS ===== */
$total_propietarios = mysqli_fetch_assoc(mysqli_query($conexion,
"SELECT COUNT(*) total FROM PROPIETARIOS"))['total'] ?? 0;
$total_residentes = mysqli_fetch_assoc(mysqli_query($conexion,
"SELECT COUNT(*) total FROM RESIDENTES"))['total'] ?? 0;
$total_usuarios = mysqli_fetch_assoc(mysqli_query($conexion,
"SELECT COUNT(*) total FROM USUARIOS"))['total'] ?? 0;

/* ===== KPI COMUNICACIONES/DOCUMENTOS ===== */
$total_comunicaciones = mysqli_fetch_assoc(mysqli_query($conexion,
"SELECT COUNT(*) total FROM COMUNICACIONES"))['total'] ?? 0;
$total_documentos = mysqli_fetch_assoc(mysqli_query($conexion,
"SELECT COUNT(*) total FROM DOCUMENTOS"))['total'] ?? 0;

/* ===== KPI PAGOS ADICIONAL ===== */
$pagos_recaudados = mysqli_fetch_assoc(mysqli_query($conexion,
"SELECT COALESCE(SUM(valor),0) total FROM PAGOS WHERE estado_pago='Pagado' $filtro_pago"))['total'] ?? 0;
$pagos_pendientes = mysqli_fetch_assoc(mysqli_query($conexion,
"SELECT COUNT(*) total FROM PAGOS WHERE estado_pago='Pendiente' $filtro_pago"))['total'] ?? 0;
$pagos_total = mysqli_fetch_assoc(mysqli_query($conexion,
"SELECT COUNT(*) total FROM PAGOS $filtro_pago"))['total'] ?? 0;

$pagos_estadistica = mysqli_fetch_assoc(mysqli_query($conexion,
"SELECT
    COUNT(*) AS cnt,
    AVG(valor) AS promedio,
    STDDEV_POP(valor) AS stddev,
    MIN(valor) AS min_valor,
    MAX(valor) AS max_valor,
    SUM(valor) AS suma
FROM PAGOS $filtro_pago"));

$pagos_cnt = $pagos_estadistica['cnt'] ?? 0;
$pagos_promedio = round($pagos_estadistica['promedio'] ?? 0, 2);
$pagos_stddev = round($pagos_estadistica['stddev'] ?? 0, 2);
$pagos_min = $pagos_estadistica['min_valor'] ?? 0;
$pagos_max = $pagos_estadistica['max_valor'] ?? 0;

/* ===== KPI POR TORRE ===== */
$torres = mysqli_query($conexion,
"SELECT t.nombre,
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

<div class="card">
Propietarios<br><br>
<h2><?php echo $total_propietarios; ?></h2>
</div>

<div class="card">
Residentes<br><br>
<h2><?php echo $total_residentes; ?></h2>
</div>

<div class="card">
Usuarios<br><br>
<h2><?php echo $total_usuarios; ?></h2>
</div>

</div>

<div class="kpi-row" style="margin-top:16px;">
<div class="card">
Comunicaciones<br><br>
<h2><?php echo $total_comunicaciones; ?></h2>
</div>

<div class="card">
Documentos<br><br>
<h2><?php echo $total_documentos; ?></h2>
</div>

<div class="card">
Pagos Pendientes<br><br>
<h2 style="color:#e74c3c;"><?php echo $pagos_pendientes; ?></h2>
</div>

<div class="card">
Pagos Rec. <br><br>
<h2 style="color:#27ae60;"><?php echo number_format($pagos_recaudados,2); ?></h2>
</div>

<div class="card">
Total Pagos<br><br>
<h2 style="color:#2980b9;"><?php echo $pagos_total; ?></h2>
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

<br>

<a href="exportar_pdf_avanzado.php" class="buttonplace">Exportar PDF</a>

<br><br>


</div>

</body>
</html>
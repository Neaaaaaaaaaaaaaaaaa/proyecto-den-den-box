<?php
include("../comun/conexion.php");

// KPI generales
$total_inmuebles = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM INMUEBLES"))['total'];
$total_residentes = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM RESIDENTES"))['total'];
$total_pagos = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM PAGOS"))['total'];
$pagos_pendientes = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM PAGOS WHERE estado_pago='Pendiente'"))['total'];
$pagos_recaudados = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COALESCE(SUM(valor),0) as total FROM PAGOS WHERE estado_pago='Pagado'"))['total'];

// Novedades
$total_novedades = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM NOVEDAD"))['total'];
$total_novedades_pendientes = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM NOVEDAD n JOIN ESTADOS_DE_NOVEDAD e ON n.id_estado=e.id_estado WHERE e.nombre_estado='Pendiente'"))['total'];

$ultimas_novedades = mysqli_query($conexion,
    "SELECT n.id_novedad, n.descripcion, n.fecha_reporte, e.nombre_estado, c.nombre_categoria, t.nombre AS torre, i.numero AS apartamento 
     FROM NOVEDAD n
     LEFT JOIN ESTADOS_DE_NOVEDAD e ON n.id_estado=e.id_estado
     LEFT JOIN CATEGORIAS_NOVEDAD c ON n.id_categoria=c.id_categoria
     LEFT JOIN INMUEBLES i ON n.id_inmueble=i.id_inmueble
     LEFT JOIN TORRES t ON i.id_torre=t.id_torre
     ORDER BY n.fecha_reporte DESC
     LIMIT 6"
);

// KPI MOROSIDAD simple
$morosos = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM PAGOS WHERE estado_pago='Pendiente'"))['total'];
$morosidad_valor = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COALESCE(SUM(valor),0) as total FROM PAGOS WHERE estado_pago='Pendiente'"))['total'];

header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>KPI Admin</title>
<style>
  body{font-family:Arial,sans-serif;padding:16px;background:#f4f6fa;color:#1d1d1f;}
  .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px;}
  .card{background:#fff;padding:16px;border-radius:12px;box-shadow:0 8px 20px rgba(48,62,87,0.08);}
  .card h3{margin:0 0 8px;font-size:16px;}
  .small-table{width:100%;border-collapse:collapse;margin-top:8px;}
  .small-table th,.small-table td{border:1px solid #d7d9e2;padding:8px;text-align:left;font-size:14px;}
  .auto-time{font-size:12px;color:#666;margin-bottom:10px;}
</style>
<meta http-equiv="refresh" content="30">
</head>
<body>
<div class="grid">
  <div class="card"><h3>Total inmuebles</h3><strong><?php echo $total_inmuebles; ?></strong></div>
  <div class="card"><h3>Total residentes</h3><strong><?php echo $total_residentes; ?></strong></div>
  <div class="card"><h3>Total novedades</h3><strong><?php echo $total_novedades; ?></strong></div>
  <div class="card"><h3>Novedades pendientes</h3><strong><?php echo $total_novedades_pendientes; ?></strong></div>
  <div class="card"><h3>Total pagos</h3><strong><?php echo $total_pagos; ?></strong></div>
  <div class="card"><h3>Pagos pendientes</h3><strong><?php echo $pagos_pendientes; ?></strong></div>
  <div class="card"><h3>Pagos recolectados</h3><strong>$ <?php echo number_format($pagos_recaudados,0,',','.'); ?></strong></div>
  <div class="card"><h3>Morosidad</h3><strong><?php echo $morosos; ?> cuotas, $ <?php echo number_format($morosidad_valor,0,',','.'); ?></strong></div>
</div>

<div class="card" style="margin-top:16px;">
  <h3>Últimas Novedades</h3>
  <table class="small-table">
    <thead><tr><th>ID</th><th>Inmueble</th><th>Categoría</th><th>Estado</th><th>Fecha</th></tr></thead>
    <tbody>
    <?php while($n = mysqli_fetch_assoc($ultimas_novedades)): ?>
    <tr>
      <td><?php echo $n['id_novedad']; ?></td>
      <td><?php echo htmlspecialchars($n['torre'].' '.$n['apartamento']); ?></td>
      <td><?php echo htmlspecialchars($n['nombre_categoria']); ?></td>
      <td><?php echo htmlspecialchars($n['nombre_estado']); ?></td>
      <td><?php echo htmlspecialchars($n['fecha_reporte']); ?></td>
    </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
</div>

</body>
</html>

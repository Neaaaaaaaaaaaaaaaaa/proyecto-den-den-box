<?php
session_start();
include("conexion.php");

$rol = $_SESSION['rol'] ?? null;
if (intval($rol) !== 1) {
    header("Location: ../html/login.html");
    exit;
}

function fecha_valida($fecha) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        return false;
    }
    $partes = explode('-', $fecha);
    return checkdate((int)$partes[1], (int)$partes[2], (int)$partes[0]);
}

$origen = isset($_GET['origen']) ? intval($_GET['origen']) : 0;
if ($origen !== 2 && $origen !== 3) {
    $origen = 0;
}

$inicio = trim($_GET['inicio'] ?? '');
$fin = trim($_GET['fin'] ?? '');
$q = trim($_GET['q'] ?? '');

$where = array();
$where[] = "u.id_rol IN (2,3)";

if ($origen === 2 || $origen === 3) {
    $where[] = "u.id_rol = $origen";
}

if ($inicio !== '' && fecha_valida($inicio)) {
    $where[] = "n.fecha_reporte >= '" . mysqli_real_escape_string($conexion, $inicio) . "'";
}

if ($fin !== '' && fecha_valida($fin)) {
    $where[] = "n.fecha_reporte <= '" . mysqli_real_escape_string($conexion, $fin) . "'";
}

if ($q !== '') {
    $q_esc = mysqli_real_escape_string($conexion, $q);
    $where[] = "(
        n.descripcion LIKE '%$q_esc%'
        OR p.nombre_completo LIKE '%$q_esc%'
        OR c.nombre_categoria LIKE '%$q_esc%'
        OR e.nombre_estado LIKE '%$q_esc%'
        OR pr.nombre LIKE '%$q_esc%'
        OR t.nombre LIKE '%$q_esc%'
        OR i.numero LIKE '%$q_esc%'
    )";
}

$where_sql = "WHERE " . implode(" AND ", $where);

$from_sql = "
FROM NOVEDAD n
LEFT JOIN USUARIOS u ON n.id_usuario = u.id_usuario
LEFT JOIN PERSONAS p ON u.id_persona = p.id_persona
LEFT JOIN ROLES r ON u.id_rol = r.id_rol
LEFT JOIN CATEGORIAS_NOVEDAD c ON n.id_categoria = c.id_categoria
LEFT JOIN ESTADOS_DE_NOVEDAD e ON n.id_estado = e.id_estado
LEFT JOIN PRIORIDADES pr ON n.id_prioridad = pr.id_prioridad
LEFT JOIN INMUEBLES i ON n.id_inmueble = i.id_inmueble
LEFT JOIN TORRES t ON i.id_torre = t.id_torre
$where_sql
";

$sql_totales = "
SELECT
    COUNT(*) AS total,
    SUM(CASE WHEN u.id_rol = 2 THEN 1 ELSE 0 END) AS total_operador,
    SUM(CASE WHEN u.id_rol = 3 THEN 1 ELSE 0 END) AS total_residente
$from_sql
";

$res_totales = mysqli_query($conexion, $sql_totales);
$totales = $res_totales ? mysqli_fetch_assoc($res_totales) : array('total' => 0, 'total_operador' => 0, 'total_residente' => 0);

$sql_listado = "
SELECT
    n.id_novedad,
    n.descripcion,
    n.fecha_reporte,
    n.fecha_cierre,
    COALESCE(r.nombre_rol, 'Sin rol') AS rol_reporta,
    COALESCE(p.nombre_completo, 'Sin nombre') AS reportado_por,
    COALESCE(c.nombre_categoria, 'Sin categoria') AS categoria,
    COALESCE(e.nombre_estado, 'Sin estado') AS estado,
    COALESCE(pr.nombre, 'Sin prioridad') AS prioridad,
    COALESCE(t.nombre, '-') AS torre,
    COALESCE(i.numero, '-') AS apartamento
$from_sql
ORDER BY n.fecha_reporte DESC, n.id_novedad DESC
LIMIT 300
";

$resultado = mysqli_query($conexion, $sql_listado);

header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Listado Novedades Admin</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body style="background:#f5f7fb;">

<div class="container" style="padding:12px;">
  <div class="kpi-row" style="margin-bottom:12px;">
    <div class="card" style="text-align:left;">
      <h3 style="margin:0 0 4px;">Total</h3>
      <strong><?php echo intval($totales['total'] ?? 0); ?></strong>
    </div>
    <div class="card" style="text-align:left;">
      <h3 style="margin:0 0 4px;">Reportadas por operador</h3>
      <strong><?php echo intval($totales['total_operador'] ?? 0); ?></strong>
    </div>
    <div class="card" style="text-align:left;">
      <h3 style="margin:0 0 4px;">Reportadas por residente</h3>
      <strong><?php echo intval($totales['total_residente'] ?? 0); ?></strong>
    </div>
  </div>

  <div class="table-container" style="margin-top:0;">
    <table class="residentes-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Origen</th>
          <th>Reportado por</th>
          <th>Inmueble</th>
          <th>Categoria</th>
          <th>Estado</th>
          <th>Prioridad</th>
          <th>Fecha reporte</th>
          <th>Fecha cierre</th>
          <th>Descripcion</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($resultado && mysqli_num_rows($resultado) > 0): ?>
        <?php while ($fila = mysqli_fetch_assoc($resultado)): ?>
          <tr>
            <td><?php echo intval($fila['id_novedad']); ?></td>
            <td><?php echo htmlspecialchars($fila['rol_reporta']); ?></td>
            <td><?php echo htmlspecialchars($fila['reportado_por']); ?></td>
            <td><?php echo htmlspecialchars($fila['torre'] . ' ' . $fila['apartamento']); ?></td>
            <td><?php echo htmlspecialchars($fila['categoria']); ?></td>
            <td><?php echo htmlspecialchars($fila['estado']); ?></td>
            <td><?php echo htmlspecialchars($fila['prioridad']); ?></td>
            <td><?php echo htmlspecialchars($fila['fecha_reporte']); ?></td>
            <td><?php echo htmlspecialchars($fila['fecha_cierre'] ?: '-'); ?></td>
            <td><?php echo nl2br(htmlspecialchars($fila['descripcion'])); ?></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td colspan="10">No hay novedades para los filtros seleccionados.</td>
        </tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>

<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['rol']) || intval($_SESSION['rol']) !== 4) {
    header("Location: ../html/login.html");
    exit;
}

$id_propietario = intval($_SESSION['id_propietario'] ?? 0);
if ($id_propietario <= 0 && isset($_SESSION['id_persona'])) {
    $id_persona = intval($_SESSION['id_persona']);
    $q_prop = mysqli_query($conexion, "SELECT id_propietario FROM PROPIETARIOS WHERE id_persona=$id_persona LIMIT 1");
    if ($q_prop && mysqli_num_rows($q_prop) > 0) {
        $id_propietario = intval(mysqli_fetch_assoc($q_prop)['id_propietario']);
    }
}

if ($id_propietario <= 0) {
    echo "<div class='container'><div class='card'>No tienes inmuebles vinculados como propietario.</div></div>";
    exit;
}

$sql = "SELECT
            n.id_novedad,
            n.descripcion,
            n.fecha_reporte,
            n.fecha_cierre,
            COALESCE(c.nombre_categoria, 'Sin categoria') AS categoria,
            COALESCE(e.nombre_estado, 'Sin estado') AS estado,
            COALESCE(pr.nombre, 'Sin prioridad') AS prioridad,
            COALESCE(t.nombre, '-') AS torre,
            COALESCE(i.numero, '-') AS apartamento,
            COALESCE(p.nombre_completo, 'Sin nombre') AS reportado_por
        FROM NOVEDAD n
        INNER JOIN INMUEBLES i ON n.id_inmueble = i.id_inmueble
        LEFT JOIN TORRES t ON i.id_torre = t.id_torre
        LEFT JOIN USUARIOS u ON n.id_usuario = u.id_usuario
        LEFT JOIN PERSONAS p ON u.id_persona = p.id_persona
        LEFT JOIN CATEGORIAS_NOVEDAD c ON n.id_categoria = c.id_categoria
        LEFT JOIN ESTADOS_DE_NOVEDAD e ON n.id_estado = e.id_estado
        LEFT JOIN PRIORIDADES pr ON n.id_prioridad = pr.id_prioridad
        WHERE i.id_propietario = $id_propietario
        ORDER BY n.fecha_reporte DESC, n.id_novedad DESC
        LIMIT 300";

$resultado = mysqli_query($conexion, $sql);
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Novedades del Propietario</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body style="background:#f5f7fb;">

<div class="container" style="padding:12px;">
  <div class="table-container" style="margin-top:0;">
    <table class="residentes-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Inmueble</th>
          <th>Reportado por</th>
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
            <td><?php echo htmlspecialchars($fila['torre'] . ' ' . $fila['apartamento']); ?></td>
            <td><?php echo htmlspecialchars($fila['reportado_por']); ?></td>
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
          <td colspan="9">No hay novedades relacionadas con tus inmuebles.</td>
        </tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>

<?php
session_start();
include("../comun/conexion.php");

$rol = intval($_SESSION['rol'] ?? 0);
if ($rol !== 3) {
    header("Location: ../../html/comun/login.html?error=sesion_expirada");
    exit;
}

$id_persona = intval($_SESSION['id_persona'] ?? 0);
$id_residente = intval($_SESSION['id_residente'] ?? 0);

if ($id_residente <= 0 && $id_persona > 0) {
    $q_res = mysqli_query($conexion, "SELECT id_residente FROM RESIDENTES WHERE id_persona = $id_persona LIMIT 1");
    if ($q_res && mysqli_num_rows($q_res) > 0) {
        $id_residente = intval(mysqli_fetch_assoc($q_res)['id_residente']);
        $_SESSION['id_residente'] = $id_residente;
    }
}

$inmuebles = [];
if ($id_residente > 0) {
    $q_inm = mysqli_query($conexion, "SELECT DISTINCT id_inmueble FROM RESIDENTE_INMUEBLE WHERE id_residente = $id_residente");
    while ($q_inm && $f = mysqli_fetch_assoc($q_inm)) {
        $inmuebles[] = intval($f['id_inmueble']);
    }
}

$novedades = [];
if (count($inmuebles) > 0) {
    $inm_filter = implode(',', $inmuebles);

    $sql = "SELECT
              n.id_novedad,
              n.descripcion,
              n.fecha_reporte,
              n.fecha_cierre,
              COALESCE(c.nombre_categoria, 'Sin categoria') AS categoria,
              COALESCE(e.nombre_estado, 'Sin estado') AS estado,
              COALESCE(pr.nombre, 'Sin prioridad') AS prioridad,
              COALESCE(t.nombre, '-') AS torre,
              COALESCE(i.numero, '-') AS apartamento
            FROM NOVEDAD n
            INNER JOIN INMUEBLES i ON n.id_inmueble = i.id_inmueble
            LEFT JOIN TORRES t ON i.id_torre = t.id_torre
            LEFT JOIN CATEGORIAS_NOVEDAD c ON n.id_categoria = c.id_categoria
            LEFT JOIN ESTADOS_DE_NOVEDAD e ON n.id_estado = e.id_estado
            LEFT JOIN PRIORIDADES pr ON n.id_prioridad = pr.id_prioridad
            WHERE n.id_inmueble IN ($inm_filter)
            ORDER BY n.fecha_reporte DESC, n.id_novedad DESC
            LIMIT 300";

    $resultado = mysqli_query($conexion, $sql);
    while ($resultado && $fila = mysqli_fetch_assoc($resultado)) {
        $novedades[] = $fila;
    }
}

function estado_class($estado)
{
    $valor = strtolower(trim((string) $estado));
    if ($valor === 'cerrado' || $valor === 'resuelto' || $valor === 'finalizado') {
        return 'estado-ok';
    }
    if ($valor === 'abierto' || $valor === 'en proceso' || $valor === 'pendiente') {
        return 'estado-pendiente';
    }
    return 'estado-neutral';
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="refresh" content="30">
<title>Novedades del residente</title>
<link rel="stylesheet" href="../../css/style.css">
<style>
  body {
    margin: 0;
    background: #f8fafc;
  }

  .table-shell {
    padding: 8px;
  }

  .table-shell table {
    margin: 0;
  }

  .estado-chip {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
  }

  .estado-ok {
    background: #e7f8ef;
    color: #067647;
    border: 1px solid #abefc6;
  }

  .estado-pendiente {
    background: #fff6e5;
    color: #b54708;
    border: 1px solid #fedf89;
  }

  .estado-neutral {
    background: #f2f4f7;
    color: #344054;
    border: 1px solid #d0d5dd;
  }

  .empty {
    text-align: center;
    padding: 20px;
    color: #667085;
  }
</style>
</head>
<body>
<div class="table-shell">
  <table class="residentes-table">
    <thead>
      <tr>
        <th>ID</th>
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
      <?php if ($id_residente <= 0): ?>
        <tr><td colspan="8" class="empty">No se pudo identificar el residente en la sesion.</td></tr>
      <?php elseif (count($inmuebles) === 0): ?>
        <tr><td colspan="8" class="empty">No tienes inmuebles asociados, por eso no hay novedades para mostrar.</td></tr>
      <?php elseif (count($novedades) === 0): ?>
        <tr><td colspan="8" class="empty">No hay novedades registradas para tus inmuebles.</td></tr>
      <?php else: ?>
        <?php foreach ($novedades as $fila): ?>
          <tr>
            <td><?php echo intval($fila['id_novedad']); ?></td>
            <td><?php echo htmlspecialchars(($fila['torre'] ?? '-') . ' ' . ($fila['apartamento'] ?? '-')); ?></td>
            <td><?php echo htmlspecialchars($fila['categoria']); ?></td>
            <td>
              <span class="estado-chip <?php echo estado_class($fila['estado']); ?>">
                <?php echo htmlspecialchars($fila['estado']); ?>
              </span>
            </td>
            <td><?php echo htmlspecialchars($fila['prioridad']); ?></td>
            <td><?php echo htmlspecialchars($fila['fecha_reporte']); ?></td>
            <td><?php echo htmlspecialchars($fila['fecha_cierre'] ?: '-'); ?></td>
            <td><?php echo nl2br(htmlspecialchars($fila['descripcion'])); ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>

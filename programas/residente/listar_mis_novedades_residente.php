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

$eventos = [];
if (count($inmuebles) > 0) {
    $inm_filter = implode(',', $inmuebles);

  $sql_novedades = "SELECT
            n.id_novedad,
            n.descripcion,
            n.fecha_reporte,
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
            LIMIT 200";

  $resultado_novedades = mysqli_query($conexion, $sql_novedades);
  while ($resultado_novedades && $fila = mysqli_fetch_assoc($resultado_novedades)) {
    $eventos[] = [
      'tipo' => 'Novedad',
      'id' => 'N-' . intval($fila['id_novedad']),
      'inmueble' => trim(($fila['torre'] ?? '-') . ' ' . ($fila['apartamento'] ?? '-')),
      'estado' => $fila['estado'],
      'prioridad' => $fila['prioridad'],
      'fecha' => $fila['fecha_reporte'],
      'titulo' => $fila['categoria'],
      'detalle' => $fila['descripcion']
    ];
  }

  $sql_pagos = "SELECT
          p.id_pago,
          p.nombre,
          p.descripcion,
          p.fecha_pago,
          p.valor,
          p.estado_pago,
          COALESCE(t.nombre, '-') AS torre,
          COALESCE(i.numero, '-') AS apartamento
          FROM PAGOS p
          INNER JOIN INMUEBLES i ON p.id_inmueble = i.id_inmueble
          LEFT JOIN TORRES t ON i.id_torre = t.id_torre
          WHERE p.id_inmueble IN ($inm_filter)
          ORDER BY p.fecha_pago DESC, p.id_pago DESC
          LIMIT 120";

  $resultado_pagos = mysqli_query($conexion, $sql_pagos);
  while ($resultado_pagos && $fila = mysqli_fetch_assoc($resultado_pagos)) {
    $detalle_pago = 'Valor: $' . number_format(floatval($fila['valor'] ?? 0), 0, ',', '.') . ' | ';
    $detalle_pago .= ($fila['descripcion'] !== null && $fila['descripcion'] !== '') ? $fila['descripcion'] : 'Sin descripcion';

    $eventos[] = [
      'tipo' => 'Pago',
      'id' => 'P-' . intval($fila['id_pago']),
      'inmueble' => trim(($fila['torre'] ?? '-') . ' ' . ($fila['apartamento'] ?? '-')),
      'estado' => $fila['estado_pago'] ?: 'Sin estado',
      'prioridad' => '-',
      'fecha' => $fila['fecha_pago'],
      'titulo' => ($fila['nombre'] ?: 'Pago registrado'),
      'detalle' => $detalle_pago
    ];
  }

  $sql_comunicaciones = "SELECT
               c.id,
               c.titulo,
               c.contenido,
               c.tipo,
               c.estado,
               c.fecha,
               COALESCE(t.nombre, '-') AS torre,
               COALESCE(i.numero, 'Global') AS apartamento
               FROM COMUNICACIONES c
               LEFT JOIN INMUEBLES i ON c.id_inmueble = i.id_inmueble
               LEFT JOIN TORRES t ON i.id_torre = t.id_torre
               WHERE (c.id_inmueble IS NULL OR c.id_inmueble IN ($inm_filter))
               AND c.estado IN ('Activa', 'Vigente', 'Prioritario')
               ORDER BY c.fecha DESC, c.id DESC
               LIMIT 120";

  $resultado_comunicaciones = mysqli_query($conexion, $sql_comunicaciones);
  while ($resultado_comunicaciones && $fila = mysqli_fetch_assoc($resultado_comunicaciones)) {
    $inmueble_com = ($fila['apartamento'] === 'Global') ? 'Comunicacion global' : trim(($fila['torre'] ?? '-') . ' ' . ($fila['apartamento'] ?? '-'));
    $eventos[] = [
      'tipo' => 'Comunicacion',
      'id' => 'C-' . intval($fila['id']),
      'inmueble' => $inmueble_com,
      'estado' => $fila['estado'] ?: 'Sin estado',
      'prioridad' => $fila['tipo'] ?: '-',
      'fecha' => $fila['fecha'],
      'titulo' => $fila['titulo'] ?: 'Comunicacion importante',
      'detalle' => $fila['contenido'] ?: 'Sin detalle'
    ];
  }
}

if ($id_residente > 0) {
  $sql_pqrs = "SELECT
          id_pqrs,
          tipo,
          asunto,
          descripcion,
          fecha,
          estado
         FROM PQRS
         WHERE id_residente = $id_residente
         ORDER BY fecha DESC, id_pqrs DESC
         LIMIT 120";

  $resultado_pqrs = mysqli_query($conexion, $sql_pqrs);
  while ($resultado_pqrs && $fila = mysqli_fetch_assoc($resultado_pqrs)) {
    $eventos[] = [
      'tipo' => 'PQRS',
      'id' => 'Q-' . intval($fila['id_pqrs']),
      'inmueble' => 'Mis solicitudes',
      'estado' => $fila['estado'] ?: 'Pendiente',
      'prioridad' => $fila['tipo'] ?: '-',
      'fecha' => $fila['fecha'],
      'titulo' => $fila['asunto'] ?: 'PQRS',
      'detalle' => $fila['descripcion'] ?: 'Sin detalle'
    ];
  }
}

usort($eventos, function ($a, $b) {
  $ta = strtotime((string) ($a['fecha'] ?? ''));
  $tb = strtotime((string) ($b['fecha'] ?? ''));

  if ($ta === $tb) {
    return strcmp((string) $b['id'], (string) $a['id']);
  }

  return $tb <=> $ta;
});

function estado_class($estado)
{
    $valor = strtolower(trim((string) $estado));
  if ($valor === 'cerrado' || $valor === 'resuelto' || $valor === 'finalizado' || $valor === 'pagado' || $valor === 'vigente') {
        return 'estado-ok';
    }
  if ($valor === 'abierto' || $valor === 'en proceso' || $valor === 'pendiente' || $valor === 'activa' || $valor === 'prioritario') {
        return 'estado-pendiente';
    }
    return 'estado-neutral';
}

function tipo_class($tipo)
{
  $valor = strtolower(trim((string) $tipo));
  if ($valor === 'pago') {
    return 'tipo-pago';
  }
  if ($valor === 'pqrs') {
    return 'tipo-pqrs';
  }
  if ($valor === 'comunicacion') {
    return 'tipo-com';
  }
  return 'tipo-novedad';
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

  .tipo-chip {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    border: 1px solid transparent;
  }

  .tipo-novedad {
    background: #eff8ff;
    color: #175cd3;
    border-color: #b2ddff;
  }

  .tipo-pago {
    background: #fdf2fa;
    color: #c11574;
    border-color: #fcceee;
  }

  .tipo-pqrs {
    background: #fff6ed;
    color: #c4320a;
    border-color: #f9dbaf;
  }

  .tipo-com {
    background: #ecfdf3;
    color: #067647;
    border-color: #abefc6;
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
        <th>Tipo</th>
        <th>ID</th>
        <th>Inmueble</th>
        <th>Titulo</th>
        <th>Estado</th>
        <th>Clasificacion</th>
        <th>Fecha</th>
        <th>Detalle</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($id_residente <= 0): ?>
        <tr><td colspan="8" class="empty">No se pudo identificar el residente en la sesion.</td></tr>
      <?php elseif (count($inmuebles) === 0): ?>
        <tr><td colspan="8" class="empty">No tienes inmuebles asociados, por eso no hay novedades para mostrar.</td></tr>
      <?php elseif (count($eventos) === 0): ?>
        <tr><td colspan="8" class="empty">No hay novedades, pagos, PQRS ni comunicaciones importantes para mostrar.</td></tr>
      <?php else: ?>
        <?php foreach ($eventos as $fila): ?>
          <tr>
            <td>
              <span class="tipo-chip <?php echo tipo_class($fila['tipo']); ?>">
                <?php echo htmlspecialchars($fila['tipo']); ?>
              </span>
            </td>
            <td><?php echo htmlspecialchars($fila['id']); ?></td>
            <td><?php echo htmlspecialchars($fila['inmueble']); ?></td>
            <td><?php echo htmlspecialchars($fila['titulo']); ?></td>
            <td>
              <span class="estado-chip <?php echo estado_class($fila['estado']); ?>">
                <?php echo htmlspecialchars($fila['estado']); ?>
              </span>
            </td>
            <td><?php echo htmlspecialchars($fila['prioridad']); ?></td>
            <td><?php echo htmlspecialchars($fila['fecha'] ?: '-'); ?></td>
            <td><?php echo nl2br(htmlspecialchars($fila['detalle'])); ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>

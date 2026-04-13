<?php
session_start();
include("../../programas/comun/conexion.php");

$rol = isset($_SESSION['id_rol']) ? intval($_SESSION['id_rol']) : intval($_SESSION['rol'] ?? 0);
$id_usuario = intval($_SESSION['id_usuario'] ?? 0);
$paquete_registrado_ok = (isset($_GET['paquete']) && $_GET['paquete'] === 'ok');

if ($id_usuario <= 0 || ($rol !== 2 && $rol !== 1)) {
  header("Location: ../comun/login.html");
  exit();
}

function tabla_existe($conexion, $nombre)
{
  $nombre = mysqli_real_escape_string($conexion, $nombre);
  $check = mysqli_query($conexion, "SHOW TABLES LIKE '$nombre'");
  return $check && mysqli_num_rows($check) > 0;
}

function columna_existe($conexion, $tabla, $columna)
{
  $tabla = mysqli_real_escape_string($conexion, $tabla);
  $columna = mysqli_real_escape_string($conexion, $columna);
  $check = mysqli_query($conexion, "SHOW COLUMNS FROM $tabla LIKE '$columna'");
  return $check && mysqli_num_rows($check) > 0;
}

$paquetes = [];
if (tabla_existe($conexion, 'paquetes')) {
  $filtro_operador = '';
  if (columna_existe($conexion, 'paquetes', 'id_usuario_registra') && $rol === 2) {
    $filtro_operador = 'WHERE id_usuario_registra = ' . $id_usuario;
  }

  $sql_paquetes = "SELECT id, residente, fecha_registro, estado
                   FROM paquetes
                   $filtro_operador
                   ORDER BY fecha_registro DESC, id DESC
                   LIMIT 8";

  $res_paquetes = mysqli_query($conexion, $sql_paquetes);
  while ($res_paquetes && $fila = mysqli_fetch_assoc($res_paquetes)) {
    $paquetes[] = $fila;
  }
}

$notificaciones = [];
if (tabla_existe($conexion, 'COMUNICACIONES')) {
  $filtro_emisor = '';
  if (columna_existe($conexion, 'COMUNICACIONES', 'id_usuario_emisor')) {
    if ($rol === 2) {
      $filtro_emisor = " AND c.id_usuario_emisor = $id_usuario";
    } else {
      $filtro_emisor = " AND c.id_usuario_emisor IN (SELECT id_usuario FROM USUARIOS WHERE id_rol = 2)";
    }
  }

  $sql_notificaciones = "SELECT c.id, c.tipo, c.fecha, c.id_inmueble,
                                COALESCE(t.nombre, '-') AS torre,
                                COALESCE(i.numero, 'Global') AS apartamento
                         FROM COMUNICACIONES c
                         LEFT JOIN INMUEBLES i ON c.id_inmueble = i.id_inmueble
                         LEFT JOIN TORRES t ON i.id_torre = t.id_torre
                         WHERE c.estado IN ('Activa', 'Vigente', 'Prioritario')
                         $filtro_emisor
                         ORDER BY c.fecha DESC, c.id DESC
                         LIMIT 8";

  $res_notificaciones = mysqli_query($conexion, $sql_notificaciones);
  while ($res_notificaciones && $fila = mysqli_fetch_assoc($res_notificaciones)) {
    $destino = 'Global';
    if (!empty($fila['id_inmueble'])) {
      $destino = trim((string) ($fila['torre'] ?? '-') . ' ' . (string) ($fila['apartamento'] ?? '-'));
    }

    $fila['destino'] = $destino;
    $notificaciones[] = $fila;
  }
}

$novedades = [];
if (tabla_existe($conexion, 'NOVEDAD')) {
  $filtro_novedades = '';
  $tiene_asignacion = columna_existe($conexion, 'NOVEDAD', 'id_usuario_asignado');

  if ($rol === 2) {
    if ($tiene_asignacion) {
      $filtro_novedades = " AND (n.id_usuario = $id_usuario OR n.id_usuario_asignado = $id_usuario)";
    } else {
      $filtro_novedades = " AND n.id_usuario = $id_usuario";
    }
  }

  $sql_novedades = "SELECT n.id_novedad, n.descripcion, n.fecha_reporte,
                           COALESCE(e.nombre_estado, 'Sin estado') AS estado
                    FROM NOVEDAD n
                    INNER JOIN USUARIOS u ON n.id_usuario = u.id_usuario
                    LEFT JOIN ESTADOS_DE_NOVEDAD e ON n.id_estado = e.id_estado
                    WHERE u.id_rol IN (2,3)
                    $filtro_novedades
                    ORDER BY n.fecha_reporte DESC, n.id_novedad DESC
                    LIMIT 8";

  $res_novedades = mysqli_query($conexion, $sql_novedades);
  while ($res_novedades && $fila = mysqli_fetch_assoc($res_novedades)) {
    $novedades[] = $fila;
  }
}

function fecha_corta($fecha)
{
  if (!$fecha) {
    return '-';
  }

  $ts = strtotime((string) $fecha);
  if ($ts === false) {
    return htmlspecialchars((string) $fecha);
  }

  return date('d/m/Y', $ts);
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" /><meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Dashboard - Operador</title>
  <link rel="shortcut icon" href="../../img/warzone.svg" type="image/x-icon">
  <link rel="stylesheet" href="../../css/style.css">
  <style>
    .alert-exito {
      background: #e7f8ef;
      border: 1px solid #abefc6;
      color: #067647;
      border-radius: 10px;
      padding: 10px 14px;
      margin: 10px 0 16px;
      font-weight: 700;
    }
  </style>
</head>
<body>
  <header class="header">
    <div class="container navbar">
      <a class="brand" href="index_operador.html"><img src="../../img/logo.png" alt="logo"><div><div class="title">Den Den Box</div><div class="subtitle">Operador</div></div></a>
      <nav class="nav-links">
        <a href="index_operador.html">Inicio</a>
        <a href="operator_dashboard.php" class="is-active">Dashboard operador</a>
        <a href="../../programas/auth/logout.php" class="btn-login">Cerrar Sesion</a>
      </nav>
    </div>
  </header>

  <main class="container app">
    <aside class="sidebar">
      <h3>Panel del Operador</h3>
      <nav class="nav">
        <a href="placeholders/registrar_paquetes.html">Registrar Paquetes</a>
        <a href="../../programas/operador/listar_tareas_operador.php">Mis Tareas</a>
      </nav>
    </aside>

<section class="dashboard-content">
  <h1>Panel del Operador</h1>

  <?php if ($paquete_registrado_ok): ?>
    <div class="alert-exito">Paquete subido con exito. Ya quedo registrado.</div>
  <?php endif; ?>

  <div class="kpi-row mt-12">

    <div class="kpi">
      <h2>Paquetes registrados</h2>
      <table class="tabla-dashboard">
        <thead>
          <tr>
            <th>ID</th>
            <th>Residente</th>
            <th>Fecha recepcion</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($paquetes) === 0): ?>
            <tr><td colspan="4">No hay registros todavia.</td></tr>
          <?php else: ?>
            <?php foreach ($paquetes as $fila): ?>
              <tr>
                <td><?php echo 'PQ-' . intval($fila['id']); ?></td>
                <td><?php echo htmlspecialchars((string) ($fila['residente'] ?? '-')); ?></td>
                <td><?php echo fecha_corta($fila['fecha_registro'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars((string) ($fila['estado'] ?? 'Sin estado')); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="kpi">
      <h2>Notificaciones enviadas</h2>
      <table class="tabla-dashboard">
        <thead>
          <tr>
            <th>ID</th>
            <th>Tipo</th>
            <th>Residente</th>
            <th>Fecha</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($notificaciones) === 0): ?>
            <tr><td colspan="4">No hay registros todavia.</td></tr>
          <?php else: ?>
            <?php foreach ($notificaciones as $fila): ?>
              <tr>
                <td><?php echo 'NT-' . intval($fila['id']); ?></td>
                <td><?php echo htmlspecialchars((string) ($fila['tipo'] ?? 'Comunicacion')); ?></td>
                <td><?php echo htmlspecialchars((string) ($fila['destino'] ?? 'Global')); ?></td>
                <td><?php echo fecha_corta($fila['fecha'] ?? ''); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="kpi">
      <h2>Novedades reportadas</h2>
      <table class="tabla-dashboard">
        <thead>
          <tr>
            <th>ID</th>
            <th>Descripcion</th>
            <th>Fecha</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($novedades) === 0): ?>
            <tr><td colspan="4">No hay registros todavia.</td></tr>
          <?php else: ?>
            <?php foreach ($novedades as $fila): ?>
              <tr>
                <td><?php echo 'NV-' . intval($fila['id_novedad']); ?></td>
                <td><?php echo htmlspecialchars((string) ($fila['descripcion'] ?? '')); ?></td>
                <td><?php echo fecha_corta($fila['fecha_reporte'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars((string) ($fila['estado'] ?? 'Sin estado')); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</section>
  </main>

  <footer class="footer container">© 2025 Den Den Box - Proyecto SENA</footer>
</body>
</html>

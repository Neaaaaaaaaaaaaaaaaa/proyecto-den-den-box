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

$inm_filter = count($inmuebles) > 0 ? implode(',', $inmuebles) : '';

$kpi_pqrs_abiertas = 0;
$kpi_saldo_pendiente = 0;
$kpi_docs = 0;
$kpi_comunicaciones = 0;

$tabla_pqrs = [];
$tabla_pagos = [];
$tabla_docs = [];
$tabla_comunicaciones = [];

if ($id_residente > 0) {
    $q_kpi_pqrs = mysqli_query(
        $conexion,
        "SELECT COUNT(*) AS total
         FROM PQRS
         WHERE id_residente = $id_residente
           AND estado NOT IN ('Cerrado', 'Finalizado', 'Resuelto')"
    );
    if ($q_kpi_pqrs) {
        $kpi_pqrs_abiertas = intval(mysqli_fetch_assoc($q_kpi_pqrs)['total'] ?? 0);
    }

    $q_pqrs = mysqli_query(
        $conexion,
        "SELECT id_pqrs, tipo, asunto, fecha, estado
         FROM PQRS
         WHERE id_residente = $id_residente
         ORDER BY fecha DESC
         LIMIT 5"
    );
    while ($q_pqrs && $f = mysqli_fetch_assoc($q_pqrs)) {
        $tabla_pqrs[] = $f;
    }
}

if ($inm_filter !== '') {
    $q_saldo = mysqli_query(
      $conexion,
      "SELECT
        COALESCE((SELECT SUM(valor) FROM PAGOS WHERE estado_pago = 'Pendiente' AND id_inmueble IN ($inm_filter)), 0)
        + COALESCE((SELECT SUM(nuevo_saldo - saldo_anterior) FROM AJUSTES_SALDO_PENDIENTE WHERE id_inmueble IN ($inm_filter)), 0)
        AS saldo_total"
    );
    if ($q_saldo) {
        $kpi_saldo_pendiente = floatval(mysqli_fetch_assoc($q_saldo)['saldo_total'] ?? 0);
        if ($kpi_saldo_pendiente < 0) {
            $kpi_saldo_pendiente = 0;
        }
    }

    $q_pagos = mysqli_query(
        $conexion,
        "SELECT p.id_pago, p.descripcion, p.fecha_pago, p.valor, i.numero AS apartamento, t.nombre AS torre
         FROM PAGOS p
         INNER JOIN INMUEBLES i ON i.id_inmueble = p.id_inmueble
         LEFT JOIN TORRES t ON t.id_torre = i.id_torre
         WHERE p.estado_pago = 'Pendiente'
           AND p.id_inmueble IN ($inm_filter)
         ORDER BY p.fecha_pago DESC, p.id_pago DESC
         LIMIT 5"
    );
    while ($q_pagos && $f = mysqli_fetch_assoc($q_pagos)) {
        $f['origen'] = 'Pago pendiente';
        $tabla_pagos[] = $f;
    }

    $q_ajustes = mysqli_query(
        $conexion,
        "SELECT a.id_ajuste,
                a.fecha_ajuste,
                (a.nuevo_saldo - a.saldo_anterior) AS valor,
                a.motivo,
                i.numero AS apartamento,
                t.nombre AS torre
         FROM AJUSTES_SALDO_PENDIENTE a
         INNER JOIN INMUEBLES i ON i.id_inmueble = a.id_inmueble
         LEFT JOIN TORRES t ON t.id_torre = i.id_torre
         WHERE a.id_inmueble IN ($inm_filter)
           AND (a.nuevo_saldo - a.saldo_anterior) > 0
         ORDER BY a.fecha_ajuste DESC, a.id_ajuste DESC
         LIMIT 5"
    );

    while ($q_ajustes && $f = mysqli_fetch_assoc($q_ajustes)) {
        $tabla_pagos[] = [
            'id_pago' => 'A-' . intval($f['id_ajuste']),
            'descripcion' => $f['motivo'] ?: 'Ajuste de deuda',
            'fecha_pago' => $f['fecha_ajuste'],
            'valor' => $f['valor'],
            'apartamento' => $f['apartamento'],
            'torre' => $f['torre'],
            'origen' => 'Ajuste operador/admin'
        ];
    }

    usort($tabla_pagos, function ($a, $b) {
        $ta = strtotime((string) ($a['fecha_pago'] ?? ''));
        $tb = strtotime((string) ($b['fecha_pago'] ?? ''));
        return $tb <=> $ta;
    });

    if (count($tabla_pagos) > 5) {
        $tabla_pagos = array_slice($tabla_pagos, 0, 5);
    }

    $doc_where = "WHERE (d.visibilidad = 'global' OR (d.visibilidad = 'inmueble' AND d.id_inmueble IN ($inm_filter)))";
    $com_where = "WHERE (c.id_inmueble IS NULL OR c.id_inmueble IN ($inm_filter))";
} else {
    $doc_where = "WHERE d.visibilidad = 'global'";
    $com_where = "WHERE c.id_inmueble IS NULL";
}

$q_kpi_docs = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM DOCUMENTOS d $doc_where");
if ($q_kpi_docs) {
    $kpi_docs = intval(mysqli_fetch_assoc($q_kpi_docs)['total'] ?? 0);
}

$q_docs = mysqli_query(
    $conexion,
    "SELECT d.tipo_documento, d.fecha_subida, d.visibilidad
     FROM DOCUMENTOS d
     $doc_where
     ORDER BY d.fecha_subida DESC, d.id_documento DESC
     LIMIT 5"
);
while ($q_docs && $f = mysqli_fetch_assoc($q_docs)) {
    $tabla_docs[] = $f;
}

$q_kpi_com = mysqli_query(
    $conexion,
    "SELECT COUNT(*) AS total
     FROM COMUNICACIONES c
     $com_where
       AND c.estado IN ('Activa', 'Vigente', 'Prioritario')"
);
if ($q_kpi_com) {
    $kpi_comunicaciones = intval(mysqli_fetch_assoc($q_kpi_com)['total'] ?? 0);
}

$q_com = mysqli_query(
    $conexion,
    "SELECT c.titulo, c.tipo, c.estado, c.fecha
     FROM COMUNICACIONES c
     $com_where
       AND c.estado IN ('Activa', 'Vigente', 'Prioritario')
     ORDER BY c.fecha DESC
     LIMIT 5"
);
while ($q_com && $f = mysqli_fetch_assoc($q_com)) {
    $tabla_comunicaciones[] = $f;
}

$ahora = date('d/m/Y H:i:s');
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <meta http-equiv="refresh" content="30" />
  <link rel="shortcut icon" href="../../img/warzone.svg" type="image/x-icon" />
  <title>Dashboard - Residente</title>
  <link rel="stylesheet" href="../../css/style.css" />
  <style>
    .dashboard-content {
      width: 100%;
      max-width: 1200px;
      margin: 0 auto;
    }

    .live-chip {
      display: inline-block;
      padding: 6px 10px;
      border-radius: 999px;
      background: #ecfdf3;
      color: #067647;
      font-weight: 700;
      font-size: 12px;
      border: 1px solid #abefc6;
    }

    .dashboard-head {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 12px;
      flex-wrap: wrap;
      margin-bottom: 18px;
      padding: 14px 16px;
      border-radius: 12px;
      background: #f8faff;
      border: 1px solid #d9e5ff;
    }

    .last-update {
      color: #475467;
      font-size: 0.9rem;
      margin: 6px 0 0;
    }

    .summary-grid {
      display: grid;
      grid-template-columns: repeat(4, minmax(180px, 1fr));
      gap: 14px;
      margin-bottom: 18px;
    }

    .summary-card {
      background: #ffffff;
      border: 1px solid #d9e5ff;
      border-radius: 12px;
      padding: 16px;
      box-shadow: 0 4px 14px rgba(16, 24, 40, 0.06);
      min-height: 130px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .summary-label {
      margin: 0;
      color: #344054;
      font-size: 0.95rem;
      font-weight: 700;
    }

    .summary-value {
      margin: 8px 0 0;
      font-size: 1.9rem;
      line-height: 1;
      color: #0b4f93;
      font-weight: 800;
    }

    .summary-note {
      margin: 8px 0 0;
      font-size: 0.85rem;
      color: #667085;
    }

    .tables-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(340px, 1fr));
      gap: 14px;
      align-items: stretch;
    }

    .table-card {
      background: #ffffff;
      border: 1px solid #d9e5ff;
      border-radius: 12px;
      box-shadow: 0 4px 14px rgba(16, 24, 40, 0.06);
      padding: 14px;
      display: flex;
      flex-direction: column;
      min-height: 320px;
    }

    .table-wrap {
      margin-top: 8px;
      overflow-x: auto;
    }

    .section-title {
      margin-top: 0;
      margin-bottom: 0;
      color: #1d2939;
      font-size: 1.02rem;
      border-bottom: 1px solid #e4ebff;
      padding-bottom: 10px;
    }

    .empty-row {
      text-align: center;
      color: #667085;
      font-style: italic;
    }

    .table-card .tabla-dashboard {
      margin: 0;
      min-width: 380px;
    }

    @media (max-width: 1200px) {
      .summary-grid {
        grid-template-columns: repeat(2, minmax(220px, 1fr));
      }

      .tables-grid {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 640px) {
      .dashboard-head {
        padding: 12px;
      }

      .summary-grid {
        grid-template-columns: 1fr;
      }

      .summary-card {
        min-height: 110px;
      }

      .summary-value {
        font-size: 1.6rem;
      }
    }
  </style>
</head>
<body>
  <header class="header">
    <div class="container navbar">
      <a class="brand" href="../../html/residente/index_residente.html">
        <img src="../../img/logo.png" alt="logo" />
        <div>
          <div class="title">Den Den Box</div>
          <div class="subtitle">Residente</div>
        </div>
      </a>
      <nav class="nav-links">
        <a href="../../html/residente/index_residente.html">Inicio</a>
        <a href="dashboard_residente.php" class="is-active">Dashboard residente</a>
        <a href="../../programas/auth/logout.php" class="btn-login">Cerrar Sesion</a>
      </nav>
    </div>
  </header>

  <main class="container app">
    <aside class="sidebar">
      <h3>Menu Residente</h3>
      <nav class="nav">
        <a href="../../html/residente/placeholders/registrar_pqrs.html">Registrar PQRS</a>
        <a href="mis_novedades_residente.php">Mis Novedades</a>
        <a href="correspondencia_residente.php">Correspondencia</a>
        <a href="../../html/residente/placeholders/registrar_pagos.html">Registrar pagos</a>
        <a href="historial_documentos.php">Documentos</a>
      </nav>
    </aside>

    <section class="dashboard-content">
      <div class="dashboard-head">
        <div>
          <h1>Panel del Residente</h1>
            <p>Bienvenido a tu espacio personal. Aquí puedes consultar el estado de tus PQRS, pagos pendientes, documentos disponibles y comunicaciones relevantes para ti.</p>
      </div>

      <div class="summary-grid">
        <div class="summary-card">
          <p class="summary-label">PQRS abiertas</p>
          <p class="summary-value"><?php echo $kpi_pqrs_abiertas; ?></p>
          <p class="summary-note">Solicitudes en seguimiento</p>
        </div>

        <div class="summary-card">
          <p class="summary-label">Saldo pendiente total</p>
          <p class="summary-value">$ <?php echo number_format($kpi_saldo_pendiente, 0, ',', '.'); ?></p>
          <p class="summary-note">Acumulado de cartera</p>
        </div>

        <div class="summary-card">
          <p class="summary-label">Documentos disponibles</p>
          <p class="summary-value"><?php echo $kpi_docs; ?></p>
          <p class="summary-note">Globales e inmueble</p>
        </div>

        <div class="summary-card">
          <p class="summary-label">Comunicaciones vigentes</p>
          <p class="summary-value"><?php echo $kpi_comunicaciones; ?></p>
          <p class="summary-note">Mensajes activos</p>
        </div>
      </div>

      <div class="tables-grid">
        <div class="table-card">
          <h3 class="section-title">Ultimas PQRS</h3>
          <div class="table-wrap">
            <table class="tabla-dashboard">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Tipo</th>
                  <th>Fecha</th>
                  <th>Estado</th>
                </tr>
              </thead>
              <tbody>
                <?php if (count($tabla_pqrs) === 0): ?>
                  <tr><td class="empty-row" colspan="4">No hay PQRS registradas.</td></tr>
                <?php else: ?>
                  <?php foreach ($tabla_pqrs as $fila): ?>
                    <tr>
                      <td><?php echo intval($fila['id_pqrs']); ?></td>
                      <td><?php echo htmlspecialchars($fila['tipo']); ?></td>
                      <td><?php echo htmlspecialchars(substr($fila['fecha'], 0, 10)); ?></td>
                      <td><?php echo htmlspecialchars($fila['estado']); ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="table-card">
          <h3 class="section-title">Pagos pendientes</h3>
          <div class="table-wrap">
            <table class="tabla-dashboard">
              <thead>
                <tr>
                  <th>Inmueble</th>
                  <th>Origen</th>
                  <th>Fecha</th>
                  <th>Valor</th>
                </tr>
              </thead>
              <tbody>
                <?php if (count($tabla_pagos) === 0): ?>
                  <tr><td class="empty-row" colspan="4">No hay pagos pendientes.</td></tr>
                <?php else: ?>
                  <?php foreach ($tabla_pagos as $fila): ?>
                    <tr>
                      <td><?php echo htmlspecialchars(($fila['torre'] ?? '-') . ' ' . ($fila['apartamento'] ?? '-')); ?></td>
                      <td><?php echo htmlspecialchars($fila['origen'] ?? 'Pago pendiente'); ?></td>
                      <td><?php echo htmlspecialchars(substr((string) ($fila['fecha_pago'] ?? ''), 0, 19)); ?></td>
                      <td>$ <?php echo number_format(floatval($fila['valor']), 0, ',', '.'); ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="table-card">
          <h3 class="section-title">Documentos recientes</h3>
          <div class="table-wrap">
            <table class="tabla-dashboard">
              <thead>
                <tr>
                  <th>Documento</th>
                  <th>Fecha</th>
                </tr>
              </thead>
              <tbody>
                <?php if (count($tabla_docs) === 0): ?>
                  <tr><td class="empty-row" colspan="2">No hay documentos disponibles.</td></tr>
                <?php else: ?>
                  <?php foreach ($tabla_docs as $fila): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($fila['tipo_documento']); ?></td>
                      <td><?php echo htmlspecialchars($fila['fecha_subida']); ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="table-card">
          <h3 class="section-title">Comunicaciones recientes</h3>
          <div class="table-wrap">
            <table class="tabla-dashboard">
              <thead>
                <tr>
                  <th>Titulo</th>
                  <th>Tipo</th>
                  <th>Fecha</th>
                </tr>
              </thead>
              <tbody>
                <?php if (count($tabla_comunicaciones) === 0): ?>
                  <tr><td class="empty-row" colspan="3">No hay comunicaciones vigentes.</td></tr>
                <?php else: ?>
                  <?php foreach ($tabla_comunicaciones as $fila): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($fila['titulo']); ?></td>
                      <td><?php echo htmlspecialchars($fila['tipo']); ?></td>
                      <td><?php echo htmlspecialchars(substr($fila['fecha'], 0, 10)); ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer class="footer container">&copy; 2026 Den Den Box - Proyecto SENA</footer>
</body>
</html>

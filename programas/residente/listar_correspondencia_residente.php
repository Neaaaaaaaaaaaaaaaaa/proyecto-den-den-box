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
$nombre_residente = "";

if ($id_persona > 0) {
    $q_persona = mysqli_query($conexion, "SELECT nombre_completo FROM PERSONAS WHERE id_persona = $id_persona LIMIT 1");
    if ($q_persona && mysqli_num_rows($q_persona) > 0) {
        $nombre_residente = trim((string) (mysqli_fetch_assoc($q_persona)['nombre_completo'] ?? ""));
    }
}

if ($id_residente <= 0 && $id_persona > 0) {
  $q_res = mysqli_query($conexion, "SELECT id_residente FROM RESIDENTES WHERE id_persona = $id_persona LIMIT 1");
  if ($q_res && mysqli_num_rows($q_res) > 0) {
    $id_residente = intval(mysqli_fetch_assoc($q_res)['id_residente']);
    $_SESSION['id_residente'] = $id_residente;
  }
}

$check_col_res = mysqli_query($conexion, "SHOW COLUMNS FROM paquetes LIKE 'id_residente'");
$tiene_col_residente = ($check_col_res && mysqli_num_rows($check_col_res) > 0);

$tiene_tabla = false;
$check_tabla = mysqli_query($conexion, "SHOW TABLES LIKE 'paquetes'");
if ($check_tabla && mysqli_num_rows($check_tabla) > 0) {
    $tiene_tabla = true;
}

$paquetes = [];
if ($tiene_tabla && $nombre_residente !== "") {
  $sql = "SELECT id, residente, empresa, observaciones, estado, fecha_registro
      FROM paquetes
      WHERE ";

  if ($tiene_col_residente && $id_residente > 0) {
    $sql .= "id_residente = ? OR ";
  }

  $sql .= "LOWER(residente) = LOWER(?)
         OR LOWER(residente) LIKE LOWER(CONCAT('%', ?, '%'))
      ORDER BY fecha_registro DESC, id DESC";

    $stmt = $conexion->prepare($sql);
    if ($stmt) {
    if ($tiene_col_residente && $id_residente > 0) {
      $stmt->bind_param("iss", $id_residente, $nombre_residente, $nombre_residente);
    } else {
      $stmt->bind_param("ss", $nombre_residente, $nombre_residente);
    }
        $stmt->execute();
        $resultado = $stmt->get_result();
        while ($resultado && $fila = $resultado->fetch_assoc()) {
            $paquetes[] = $fila;
        }
        $stmt->close();
    }
}

function estado_class($estado)
{
    $valor = strtolower(trim((string) $estado));
    if ($valor === 'entregado') {
        return 'estado-ok';
    }
    if ($valor === 'pendiente' || $valor === 'pendiente para reclamar') {
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
<title>Correspondencia del residente</title>
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
        <th>Empresa</th>
        <th>Observaciones</th>
        <th>Estado</th>
        <th>Fecha</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$tiene_tabla): ?>
        <tr><td colspan="5" class="empty">No existe la tabla de paquetes en la base de datos.</td></tr>
      <?php elseif ($nombre_residente === ""): ?>
        <tr><td colspan="5" class="empty">No se pudo identificar el nombre del residente en la sesion.</td></tr>
      <?php elseif (count($paquetes) === 0): ?>
        <tr><td colspan="5" class="empty">No hay correspondencia registrada para este residente.</td></tr>
      <?php else: ?>
        <?php foreach ($paquetes as $fila): ?>
          <tr>
            <td><?php echo intval($fila['id']); ?></td>
            <td><?php echo htmlspecialchars($fila['empresa']); ?></td>
            <td><?php echo htmlspecialchars($fila['observaciones']); ?></td>
            <td>
              <span class="estado-chip <?php echo estado_class($fila['estado']); ?>">
                <?php echo htmlspecialchars($fila['estado']); ?>
              </span>
            </td>
            <td><?php echo htmlspecialchars($fila['fecha_registro']); ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>

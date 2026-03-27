<?php
include(__DIR__ . '/../comun/conexion.php');

mysqli_query($conexion, "CREATE TABLE IF NOT EXISTS AJUSTES_SALDO_PENDIENTE (
  id_ajuste INT AUTO_INCREMENT PRIMARY KEY,
  id_inmueble INT NOT NULL,
  saldo_anterior DECIMAL(12,2) NOT NULL,
  nuevo_saldo DECIMAL(12,2) NOT NULL,
  motivo VARCHAR(255) DEFAULT 'Ajuste manual por administrador',
  id_usuario_admin INT NULL,
  fecha_ajuste DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_inmueble) REFERENCES INMUEBLES(id_inmueble),
  FOREIGN KEY (id_usuario_admin) REFERENCES USUARIOS(id_usuario)
)");

$id_inmueble = isset($_GET['id_inmueble']) ? intval($_GET['id_inmueble']) : 0;
$torre = trim($_GET['torre'] ?? '');
$inicio = trim($_GET['inicio'] ?? '');
$fin = trim($_GET['fin'] ?? '');

$validDate = function($d){
  return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) !== false;
};
if($inicio !== '' && !$validDate($inicio)) $inicio = '';
if($fin !== '' && !$validDate($fin)) $fin = '';
if($inicio !== '' && $fin !== '' && $inicio > $fin){
  [$inicio, $fin] = [$fin, $inicio];
}

$filtro_pagos = '';
if($inicio !== '' && $fin !== ''){
  $filtro_pagos = "WHERE fecha_pago BETWEEN '$inicio' AND '$fin'";
} elseif($inicio !== ''){
  $filtro_pagos = "WHERE fecha_pago >= '$inicio'";
} elseif($fin !== ''){
  $filtro_pagos = "WHERE fecha_pago <= '$fin'";
}

$condiciones = [];
if($id_inmueble > 0){
  $condiciones[] = "i.id_inmueble = $id_inmueble";
}
if($torre !== ''){
  $torre_sql = mysqli_real_escape_string($conexion, $torre);
  $condiciones[] = "t.nombre = '$torre_sql'";
}
$condicion = '';
if(count($condiciones) > 0){
  $condicion = 'WHERE ' . implode(' AND ', $condiciones);
}

$estado_mensaje = "";
if(isset($_GET['status'])){
  if($_GET['status'] === 'updated'){
    $estado_mensaje = "Saldo pendiente incrementado correctamente.";
  } elseif($_GET['status'] === 'invalid'){
    $estado_mensaje = "No fue posible actualizar: datos inválidos.";
  } elseif($_GET['status'] === 'error'){
    $estado_mensaje = "Se presentó un error al guardar el ajuste.";
  } elseif($_GET['status'] === 'unauthorized'){
    $estado_mensaje = "Acceso no autorizado para modificar saldos.";
  }
}

$sql = "SELECT i.id_inmueble,
               t.nombre AS torre,
               i.numero AS apartamento,
               COALESCE(p_prop.nombre_completo,'-') AS propietario,
         pagos.ultimo_pago,
         COALESCE(pagos.total_pagado, 0) AS total_pagado,
         (COALESCE(pagos.saldo_pendiente_base, 0) + COALESCE(ajustes.total_ajuste, 0)) AS saldo_pendiente,
         COALESCE(pagos.numero_pagos, 0) AS numero_pagos
        FROM INMUEBLES i
        INNER JOIN TORRES t ON i.id_torre = t.id_torre
        LEFT JOIN PROPIETARIOS prop ON i.id_propietario = prop.id_propietario
        LEFT JOIN PERSONAS p_prop ON prop.id_persona = p_prop.id_persona
    LEFT JOIN (
      SELECT id_inmueble,
           MAX(fecha_pago) AS ultimo_pago,
           SUM(CASE WHEN estado_pago='Pagado' THEN valor ELSE 0 END) AS total_pagado,
           SUM(CASE WHEN estado_pago='Pendiente' THEN valor ELSE 0 END) AS saldo_pendiente_base,
           COUNT(id_pago) AS numero_pagos
       FROM PAGOS
       $filtro_pagos
      GROUP BY id_inmueble
    ) pagos ON pagos.id_inmueble = i.id_inmueble
    LEFT JOIN (
      SELECT id_inmueble,
           SUM(nuevo_saldo - saldo_anterior) AS total_ajuste
      FROM AJUSTES_SALDO_PENDIENTE
      GROUP BY id_inmueble
    ) ajustes ON ajustes.id_inmueble = i.id_inmueble
        $condicion
        ORDER BY t.nombre, i.numero";

$resultado = mysqli_query($conexion, $sql);

$reloadInterval = 30; // segundos
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Pagos en tiempo real</title>
<meta http-equiv="refresh" content="<?php echo $reloadInterval; ?>">
<link rel="stylesheet" href="../../css/style.css">
<style>
  .ajuste-form {
    display: flex;
    align-items: center;
    gap: 6px;
    justify-content: center;
  }
  .ajuste-form input {
    width: 120px;
    padding: 6px;
    border: 1px solid #c7d0dd;
    border-radius: 6px;
  }
  .ajuste-form button {
    background: #1f6feb;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 6px 10px;
    font-weight: 700;
    cursor: pointer;
  }
  .msg-estado {
    margin: 10px 0;
    padding: 10px;
    border-radius: 8px;
    background: #eef6ff;
    color: #0b4f93;
    border: 1px solid #b9d9ff;
  }
</style>
</head>
<body>
<div class="container">
  <?php if($estado_mensaje !== ""): ?>
    <div class="msg-estado"><?php echo htmlspecialchars($estado_mensaje); ?></div>
  <?php endif; ?>

  <table class="tabla-dashboard tabla-pagos">
    <thead>
      <tr>
        <th>ID</th>
        <th>Torre-Apto</th>
        <th>Propietario</th>
        <th>Último pago</th>
        <th>Número pagos</th>
        <th>Total pagado</th>
        <th>Saldo pendiente</th>
        <th>Estado</th>
        <th>Agregar al saldo pendiente</th>
      </tr>
    </thead>
    <tbody>
    <?php while($fila = mysqli_fetch_assoc($resultado)): ?>
      <?php
        $saldo_pendiente_num = floatval($fila['saldo_pendiente']);
        $estado = ($saldo_pendiente_num > 0) ? 'En mora' : 'Al día';
        $total_pagado = number_format($fila['total_pagado'], 0, ',', '.');
        $saldo_pendiente = number_format($saldo_pendiente_num, 0, ',', '.');
      ?>
      <tr>
        <td><?php echo $fila['id_inmueble']; ?></td>
        <td><?php echo htmlspecialchars($fila['torre'].' - '.$fila['apartamento']); ?></td>
        <td><?php echo htmlspecialchars($fila['propietario']); ?></td>
        <td><?php echo $fila['ultimo_pago'] ?: '-'; ?></td>
        <td><?php echo $fila['numero_pagos']; ?></td>
        <td>$ <?php echo $total_pagado; ?></td>
        <td>$ <?php echo $saldo_pendiente; ?></td>
        <td><?php echo $estado; ?></td>
        <td>
          <form class="ajuste-form" method="post" action="actualizar_saldo_pendiente.php" target="_self" onsubmit="return confirm('¿Confirmas agregar este valor al saldo pendiente del inmueble?');">
            <input type="hidden" name="id_inmueble" value="<?php echo intval($fila['id_inmueble']); ?>">
            <input type="number" name="monto_agregar" min="0" step="0.01" value="0.00" required>
            <button type="submit">Agregar</button>
          </form>
        </td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>

</div>
</body>
</html>

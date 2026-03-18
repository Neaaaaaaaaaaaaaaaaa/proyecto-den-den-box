<?php
include("conexion.php");

$id_inmueble = isset($_GET['id_inmueble']) ? intval($_GET['id_inmueble']) : 0;
$condicion = "";
if($id_inmueble > 0){
    $condicion = "WHERE i.id_inmueble = $id_inmueble";
}

$sql = "SELECT i.id_inmueble,
               t.nombre AS torre,
               i.numero AS apartamento,
               COALESCE(p_prop.nombre_completo,'-') AS propietario,
               MAX(p.fecha_pago) AS ultimo_pago,
               SUM(CASE WHEN p.estado_pago='Pagado' THEN p.valor ELSE 0 END) AS total_pagado,
               SUM(CASE WHEN p.estado_pago='Pendiente' THEN p.valor ELSE 0 END) AS saldo_pendiente,
               COUNT(p.id_pago) AS numero_pagos
        FROM INMUEBLES i
        INNER JOIN TORRES t ON i.id_torre = t.id_torre
        LEFT JOIN PROPIETARIOS prop ON i.id_propietario = prop.id_propietario
        LEFT JOIN PERSONAS p_prop ON prop.id_persona = p_prop.id_persona
        LEFT JOIN PAGOS p ON i.id_inmueble = p.id_inmueble
        $condicion
        GROUP BY i.id_inmueble, t.nombre, i.numero, p_prop.nombre_completo
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
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
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
      </tr>
    </thead>
    <tbody>
    <?php while($fila = mysqli_fetch_assoc($resultado)): ?>
      <?php
        $estado = ($fila['saldo_pendiente'] > 0) ? 'En mora' : 'Al día';
        $total_pagado = number_format($fila['total_pagado'], 0, ',', '.');
        $saldo_pendiente = number_format($fila['saldo_pendiente'], 0, ',', '.');
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
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>

</div>
</body>
</html>

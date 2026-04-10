<?php
include("../comun/conexion.php");

$sql = "SELECT
          u.id_usuario,
          p.id_persona,
          p.nombre_completo,
          p.tipo_documento,
          p.numero_documento,
          p.telefono,
          p.correo,
          r.nombre_rol,
          u.id_rol,
          u.estado,
          COALESCE((
            SELECT GROUP_CONCAT(DISTINCT CONCAT(t2.nombre, '-', i2.numero) SEPARATOR ', ')
            FROM RESIDENTES r2
            INNER JOIN RESIDENTE_INMUEBLE ri2 ON r2.id_residente = ri2.id_residente
            INNER JOIN INMUEBLES i2 ON i2.id_inmueble = ri2.id_inmueble
            INNER JOIN TORRES t2 ON t2.id_torre = i2.id_torre
            WHERE r2.id_persona = p.id_persona
          ), '-') AS inmuebles_residente,
          COALESCE((
            SELECT GROUP_CONCAT(DISTINCT CONCAT(t3.nombre, '-', i3.numero) SEPARATOR ', ')
            FROM PROPIETARIOS pr3
            INNER JOIN INMUEBLES i3 ON i3.id_propietario = pr3.id_propietario
            INNER JOIN TORRES t3 ON t3.id_torre = i3.id_torre
            WHERE pr3.id_persona = p.id_persona
          ), '-') AS inmuebles_propietario
        FROM USUARIOS u
        INNER JOIN PERSONAS p ON p.id_persona = u.id_persona
        INNER JOIN ROLES r ON r.id_rol = u.id_rol
        ORDER BY p.nombre_completo";

$resultado = mysqli_query($conexion, $sql);
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Gestión de usuarios</title>
<link rel="stylesheet" href="../../css/style.css">
<style>
  .acciones { display: flex; gap: 8px; justify-content: center; }
  .btn-mini {
    padding: 6px 10px;
    border-radius: 6px;
    text-decoration: none;
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    border: none;
    cursor: pointer;
  }
  .btn-editar { background: #0f766e; }
  .btn-eliminar { background: #b42318; }
  .btn-estado-activo { background: #027a48; }
  .btn-estado-inactivo { background: #b54708; }
  .estado-chip {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    color: #fff;
  }
  .estado-activo { background: #027a48; }
  .estado-inactivo { background: #b54708; }
</style>
</head>
<body>
<div class="table-container">
<table class="residentes-table">
  <thead>
    <tr>
      <th>Nombre</th>
      <th>Documento</th>
      <th>Teléfono</th>
      <th>Correo</th>
      <th>Rol</th>
      <th>Estado</th>
      <th>Inmueble</th>
      <th>Acciones</th>
    </tr>
  </thead>
  <tbody>
  <?php while($fila = mysqli_fetch_assoc($resultado)): ?>
    <?php
      $inmueble = ($fila['id_rol'] == 3) ? $fila['inmuebles_residente'] : (($fila['id_rol'] == 4) ? $fila['inmuebles_propietario'] : '-');
      $estado_raw = trim((string)$fila['estado']);
      $es_activo = ($estado_raw === '1' || strcasecmp($estado_raw, 'Activo') === 0);
      $estado_texto = $es_activo ? 'Activo' : 'Inactivo';
      $nuevo_estado = $es_activo ? '0' : '1';
      $clase_estado = $es_activo ? 'estado-chip estado-activo' : 'estado-chip estado-inactivo';
      $clase_boton_estado = $es_activo ? 'btn-mini btn-estado-inactivo' : 'btn-mini btn-estado-activo';
      $texto_boton_estado = $es_activo ? 'Inactivar' : 'Activar';
    ?>
    <tr>
      <td><?php echo htmlspecialchars($fila['nombre_completo']); ?></td>
      <td><?php echo htmlspecialchars($fila['tipo_documento'].' '.$fila['numero_documento']); ?></td>
      <td><?php echo htmlspecialchars($fila['telefono']); ?></td>
      <td><?php echo htmlspecialchars($fila['correo']); ?></td>
      <td><?php echo htmlspecialchars($fila['nombre_rol']); ?></td>
      <td><span class="<?php echo $clase_estado; ?>"><?php echo $estado_texto; ?></span></td>
      <td><?php echo htmlspecialchars($inmueble); ?></td>
      <td>
        <div class="acciones">
          <a class="btn-mini btn-editar" href="editar_usuario.php?id_usuario=<?php echo intval($fila['id_usuario']); ?>" target="_top">Modificar</a>
          <form method="post" action="actualizar_estado_usuario.php" target="_top" onsubmit="return confirm('¿Deseas cambiar el estado de este usuario?');">
            <input type="hidden" name="id_usuario" value="<?php echo intval($fila['id_usuario']); ?>">
            <input type="hidden" name="nuevo_estado" value="<?php echo $nuevo_estado; ?>">
            <button type="submit" class="<?php echo $clase_boton_estado; ?>"><?php echo $texto_boton_estado; ?></button>
          </form>
          <form method="post" action="eliminar_usuario.php" target="_top" onsubmit="return confirm('¿Eliminar este usuario y sus datos asociados?');">
            <input type="hidden" name="id_usuario" value="<?php echo intval($fila['id_usuario']); ?>">
            <button type="submit" class="btn-mini btn-eliminar">Eliminar</button>
          </form>
        </div>
      </td>
    </tr>
  <?php endwhile; ?>
  </tbody>
</table>
</div>
</body>
</html>

<?php
include("conexion.php");

$sql = "SELECT * FROM pagos";
$resultado = mysqli_query($conexion, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Ver Pagos</title>
</head>
<body>

<h2>Pagos registrados</h2>

<table borde="1" cellpadding="10">
  <tr>
    <th>nombre</th>
    <th>descripción</th>
    <th>archivo</th>
  </tr>

  <?php while($fila = mysqli_fetch_assoc($resultado)){ ?>
    <tr>
      <td><?php echo $fila['nombre']; ?></td>
      <td><?php echo $fila['descripcion']; ?></td>
      <td>
        <a href="../uploads/<?php echo $fila['archivo']; ?>" target="_blank">
          Ver archivo
        </a>
      </td>
    </tr>
  <?php } ?>

</table>

</body>
</html>
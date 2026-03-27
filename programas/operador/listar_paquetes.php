<?php
session_start();
include("../comun/conexion.php");

$rol = isset($_SESSION['id_rol']) ? intval($_SESSION['id_rol']) : intval($_SESSION['rol'] ?? 0);
if (!isset($_SESSION['id_usuario']) || ($rol !== 2 && $rol !== 1)) {
	header("Location: ../../html/comun/login.html");
	exit();
}

$sql = "SELECT id, residente, empresa, observaciones, estado, fecha_registro FROM paquetes ORDER BY fecha_registro DESC";
$resultado = mysqli_query($conexion, $sql);
?>
<!doctype html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Historial de Paquetes</title>
	<link rel="stylesheet" href="../../css/style.css">
</head>
<body>
	<h1>Historial de Paquetes Registrados</h1>
	<table class="tabla-dashboard">
		<thead>
			<tr>
				<th>ID</th>
				<th>Residente</th>
				<th>Empresa</th>
				<th>Observaciones</th>
				<th>Estado</th>
				<th>Fecha Registro</th>
			</tr>
		</thead>
		<tbody>
			<?php if ($resultado && mysqli_num_rows($resultado) > 0): ?>
				<?php while ($fila = mysqli_fetch_assoc($resultado)): ?>
					<tr>
						<td><?php echo intval($fila['id']); ?></td>
						<td><?php echo htmlspecialchars($fila['residente']); ?></td>
						<td><?php echo htmlspecialchars($fila['empresa']); ?></td>
						<td><?php echo htmlspecialchars($fila['observaciones']); ?></td>
						<td><?php echo htmlspecialchars($fila['estado']); ?></td>
						<td><?php echo htmlspecialchars($fila['fecha_registro']); ?></td>
					</tr>
				<?php endwhile; ?>
			<?php else: ?>
				<tr><td colspan="6">No hay paquetes registrados.</td></tr>
			<?php endif; ?>
		</tbody>
	</table>
</body>
</html>
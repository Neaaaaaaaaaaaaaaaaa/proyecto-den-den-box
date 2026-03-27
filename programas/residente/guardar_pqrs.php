<?php
include '../comun/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$tipo = $_POST['tipo'];
	$asunto = $_POST['asunto'];
	$descripcion = $_POST['descripcion'];
	$residente_id = 1;

	$sql = "INSERT INTO pqrs (residente_id, tipo, asunto, descripcion, fecha, estado) VALUES (?, ?, ?, ?, NOW(), 'pendiente')";
	$stmt = $conexion->prepare($sql);
	$stmt->bind_param("isss", $residente_id, $tipo, $asunto, $descripcion);

	if ($stmt->execute()) {
		echo "<script>alert('PQRS registrado exitosamente'); window.location.href='../../html/residente/placeholders/registrar_pqrs.html';</script>";
	} else {
		echo "<script>alert('Error al registrar PQRS: " . $stmt->error . "'); window.history.back();</script>";
	}

	$stmt->close();
	$conexion->close();
} else {
	header("Location: ../../html/residente/placeholders/registrar_pqrs.html");
	exit();
}
?>

<?php
session_start();
include("../comun/conexion.php");

if(!$conexion){
		die("Error: no hay conexion con la base de datos");
}

if(!isset($_SESSION['id_usuario']) || intval($_SESSION['id_usuario']) <= 0){
		header("Location: ../../html/comun/login.html");
		exit();
}

$id_usuario = intval($_SESSION['id_usuario']);

if($_SERVER['REQUEST_METHOD'] === 'POST'){
		$nueva_contrasena = isset($_POST['nueva_contrasena']) ? trim($_POST['nueva_contrasena']) : '';
		$confirmar_contrasena = isset($_POST['confirmar_contrasena']) ? trim($_POST['confirmar_contrasena']) : '';

		if($nueva_contrasena === '' || $confirmar_contrasena === ''){
				echo "<script>alert('Debes completar los campos de contrasena');window.history.back();</script>";
				exit();
		}

		if($nueva_contrasena !== $confirmar_contrasena){
				echo "<script>alert('Las contrasenas no coinciden');window.history.back();</script>";
				exit();
		}

		$stmt = mysqli_prepare($conexion, "UPDATE USUARIOS SET contraseña = ? WHERE id_usuario = ? LIMIT 1");
		if(!$stmt){
				echo "<script>alert('No se pudo preparar la actualizacion');window.history.back();</script>";
				exit();
		}

		mysqli_stmt_bind_param($stmt, "si", $nueva_contrasena, $id_usuario);
		$ok = mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);

		if(!$ok){
				echo "<script>alert('No se pudo actualizar la contrasena');window.history.back();</script>";
				exit();
		}

		echo "<script>alert('Contrasena actualizada correctamente');window.location.href='../../html/comun/login.html';</script>";
		exit();
}
?>
<!doctype html>
<html lang="es">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<title>Cambiar Contrasena - Den Den Box</title>
	<link rel="shortcut icon" href="../../img/warzone.svg" type="image/x-icon">
	<link rel="stylesheet" href="../../css/style.css">
</head>
<body>

<header class="header">
	<div class="container navbar">
		<a class="brand" href="../../html/comun/index.html">
			<img src="../../img/logo.png" alt="Den Den Box">
			<div>
				<div class="title">Den Den Box</div>
				<div class="subtitle">Actualizar acceso</div>
			</div>
		</a>

		<nav class="nav-links">
			<a href="../../html/comun/index.html">Inicio</a>
			<a href="../../html/comun/login.html" class="btn-login">Iniciar Sesion</a>
		</nav>
	</div>
</header>

<main class="container" style="min-height:calc(100vh - 160px);display:flex;flex-direction:column;justify-content:center;align-items:center;">
	<h1 style="text-align:center;margin-bottom:14px;">Cambiar contrasena</h1>

	<div style="width:100%;max-width:420px;background:white;padding:22px;border-radius:12px;box-shadow:0 6px 18px rgba(0,0,0,0.06);">
		<form action="cambiar_contrasena_usuario.php" method="POST">
			<label class="login-label">Nueva contrasena</label>
			<input type="password" class="login-input" name="nueva_contrasena" required>

			<br><br>

			<label class="login-label">Confirmar nueva contrasena</label>
			<input type="password" class="login-input" name="confirmar_contrasena" required>

			<br><br>

			<input type="submit" class="login-btn" value="Actualizar contrasena">
		</form>
	</div>
</main>

<section class="footer">© 2025 Den Den Box - Proyecto SENA</section>

</body>
</html>

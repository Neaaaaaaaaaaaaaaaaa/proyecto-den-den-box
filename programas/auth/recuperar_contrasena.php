<?php
include("../comun/conexion.php");

if(!$conexion){
		die("Error: no hay conexion con la base de datos");
}

function post_value($key, $default = "") {
		return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}

$accion = post_value('accion');

if($accion === 'verificar_correo'){
		$correo = mysqli_real_escape_string($conexion, post_value('correo'));

		if($correo === ''){
				echo "<script>alert('Debes ingresar un correo');window.history.back();</script>";
				exit();
		}

		$sql = "SELECT u.id_usuario, p.correo
						FROM USUARIOS u
						INNER JOIN PERSONAS p ON u.id_persona = p.id_persona
						WHERE p.correo = '$correo'
						LIMIT 1";

		$res = mysqli_query($conexion, $sql);

		if(!$res || mysqli_num_rows($res) === 0){
				echo "<script>alert('El correo no esta registrado');window.location.href='../../html/comun/olvide_contrasena.html';</script>";
				exit();
		}

		$fila = mysqli_fetch_assoc($res);
		$id_usuario = intval($fila['id_usuario']);
		$correo_mostrar = htmlspecialchars($fila['correo']);
		?>
		<!doctype html>
		<html lang="es">
		<head>
			<meta charset="utf-8" />
			<meta name="viewport" content="width=device-width,initial-scale=1" />
			<title>Nueva Contrasena - Den Den Box</title>
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
						<div class="subtitle">Recuperar acceso</div>
					</div>
				</a>

				<nav class="nav-links">
					<a href="../../html/comun/index.html">Inicio</a>
					<a href="../../html/comun/login.html" class="btn-login">Iniciar Sesion</a>
				</nav>
			</div>
		</header>

		<main class="container" style="min-height:calc(100vh - 160px);display:flex;flex-direction:column;justify-content:center;align-items:center;">

			<h1 style="text-align:center;margin-bottom:14px;">Crear nueva contrasena</h1>

			<div style="width:100%;max-width:420px;background:white;padding:22px;border-radius:12px;box-shadow:0 6px 18px rgba(0,0,0,0.06);">
				<p style="font-size:14px;color:#475467;margin:0 0 14px 0;">Correo verificado: <strong><?php echo $correo_mostrar; ?></strong></p>

				<form action="recuperar_contrasena.php" method="POST">
					<input type="hidden" name="accion" value="actualizar_contrasena">
					<input type="hidden" name="id_usuario" value="<?php echo $id_usuario; ?>">

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
		<?php
		exit();
}

if($accion === 'actualizar_contrasena'){
		$id_usuario = intval(post_value('id_usuario', '0'));
		$nueva = post_value('nueva_contrasena');
		$confirmar = post_value('confirmar_contrasena');

		if($id_usuario <= 0 || $nueva === '' || $confirmar === ''){
				echo "<script>alert('Datos incompletos para actualizar contrasena');window.location.href='../../html/comun/olvide_contrasena.html';</script>";
				exit();
		}

		if($nueva !== $confirmar){
				echo "<script>alert('Las contrasenas no coinciden');window.history.back();</script>";
				exit();
		}

		$nueva_sql = mysqli_real_escape_string($conexion, $nueva);
		$sql_upd = "UPDATE USUARIOS SET contraseña='$nueva_sql' WHERE id_usuario=$id_usuario LIMIT 1";

		if(!mysqli_query($conexion, $sql_upd)){
				echo "<script>alert('No se pudo actualizar la contrasena');window.history.back();</script>";
				exit();
		}

		if(mysqli_affected_rows($conexion) < 0){
				echo "<script>alert('No se pudo actualizar la contrasena');window.history.back();</script>";
				exit();
		}

		echo "<script>alert('Contrasena actualizada correctamente');window.location.href='../../html/comun/login.html';</script>";
		exit();
}

echo "<script>window.location.href='../../html/comun/olvide_contrasena.html';</script>";
exit();
?>

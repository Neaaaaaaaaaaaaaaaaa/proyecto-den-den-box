<?php
include("../comun/conexion.php");

$id_usuario = isset($_GET['id_usuario']) ? intval($_GET['id_usuario']) : 0;
if($id_usuario <= 0){
		die("Usuario inválido");
}

$sql = "SELECT u.id_usuario, u.id_rol, u.contraseña, p.id_persona, p.nombre_completo, p.tipo_documento, p.numero_documento, p.telefono, p.correo,
							 COALESCE(res.profesion,'') AS profesion
				FROM USUARIOS u
				INNER JOIN PERSONAS p ON p.id_persona = u.id_persona
				LEFT JOIN RESIDENTES res ON res.id_persona = p.id_persona
				WHERE u.id_usuario = $id_usuario
				LIMIT 1";

$resultado = mysqli_query($conexion, $sql);
if(!$resultado || mysqli_num_rows($resultado) === 0){
		die("Usuario no encontrado");
}

$u = mysqli_fetch_assoc($resultado);
$id_persona = intval($u['id_persona']);

$numero_inmueble = '';
$torre_inmueble = '';

if(intval($u['id_rol']) === 3){
		$q = mysqli_query($conexion, "SELECT i.numero, t.nombre
																FROM RESIDENTES r
																INNER JOIN RESIDENTE_INMUEBLE ri ON ri.id_residente = r.id_residente
																INNER JOIN INMUEBLES i ON i.id_inmueble = ri.id_inmueble
																INNER JOIN TORRES t ON t.id_torre = i.id_torre
																WHERE r.id_persona = $id_persona
																LIMIT 1");
		if($q && mysqli_num_rows($q) > 0){
				$x = mysqli_fetch_assoc($q);
				$numero_inmueble = $x['numero'];
				$torre_inmueble = $x['nombre'];
		}
}

if(intval($u['id_rol']) === 4){
		$q = mysqli_query($conexion, "SELECT i.numero, t.nombre
																FROM PROPIETARIOS pr
																INNER JOIN INMUEBLES i ON i.id_propietario = pr.id_propietario
																INNER JOIN TORRES t ON t.id_torre = i.id_torre
																WHERE pr.id_persona = $id_persona
																LIMIT 1");
		if($q && mysqli_num_rows($q) > 0){
				$x = mysqli_fetch_assoc($q);
				$numero_inmueble = $x['numero'];
				$torre_inmueble = $x['nombre'];
		}
}

$emergencia = ['nombre' => '', 'telefono' => '', 'relacion' => ''];
$mascota = ['tipo' => '', 'raza' => '', 'cantidad' => ''];

$q_em = mysqli_query($conexion, "SELECT ce.nombre, ce.telefono, ce.relacion
																FROM RESIDENTES r
																INNER JOIN CONTACTOS_DE_EMERGENCIA ce ON ce.id_residente = r.id_residente
																WHERE r.id_persona = $id_persona
																LIMIT 1");
if($q_em && mysqli_num_rows($q_em) > 0){
		$emergencia = mysqli_fetch_assoc($q_em);
}

$q_ma = mysqli_query($conexion, "SELECT m.tipo, m.raza, m.cantidad
																FROM RESIDENTES r
																INNER JOIN MASCOTAS m ON m.id_residente = r.id_residente
																WHERE r.id_persona = $id_persona
																LIMIT 1");
if($q_ma && mysqli_num_rows($q_ma) > 0){
		$mascota = mysqli_fetch_assoc($q_ma);
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Modificar Usuario</title>
<link rel="stylesheet" href="../../css/style.css">
</head>
<body>
<header class="header">
	<div class="container navbar">
		<a class="brand" href="../../html/admin/crear.html">
			<img src="../../img/logo.png">
			<div>
				<div class="title">Den Den Box</div>
				<div class="subtitle">Modificar usuario</div>
			</div>
		</a>
		<nav class="nav-links">
			<a href="../../html/admin/crear.html">Volver</a>
		</nav>
	</div>
</header>

<main class="container" style="padding-top:24px;max-width:760px;">
<div style="background:white;padding:20px;border-radius:12px;box-shadow:0 6px 18px rgba(0,0,0,0.06);">
<form method="post" action="actualizar_usuario.php">
	<input type="hidden" name="id_usuario" value="<?php echo intval($u['id_usuario']); ?>">

	<label>Nombre completo</label>
	<input class="login-input" type="text" name="nombre" required value="<?php echo htmlspecialchars($u['nombre_completo']); ?>">

	<label>Tipo documento</label>
	<select class="login-input" name="documento" required>
		<?php $tipos = ['CC','TI','CEDULA DE EXTRANJERIA','PASAPORTE']; foreach($tipos as $tp): ?>
			<option value="<?php echo $tp; ?>" <?php echo ($u['tipo_documento'] === $tp ? 'selected' : ''); ?>><?php echo $tp; ?></option>
		<?php endforeach; ?>
	</select>

	<label>Número documento</label>
	<input class="login-input" type="number" name="num_doc" required value="<?php echo htmlspecialchars($u['numero_documento']); ?>">

	<label>Teléfono</label>
	<input class="login-input" type="number" name="num_tlf" required value="<?php echo htmlspecialchars($u['telefono']); ?>">

	<label>Correo</label>
	<input class="login-input" type="email" name="correo" required value="<?php echo htmlspecialchars($u['correo']); ?>">

	<label>Contraseña</label>
	<input class="login-input" type="text" name="contrasena" required value="<?php echo htmlspecialchars($u['contraseña']); ?>">

	<label>Rol</label>
	<select class="login-input" name="rol" required>
		<option value="3" <?php echo (intval($u['id_rol']) === 3 ? 'selected' : ''); ?>>Residente</option>
		<option value="2" <?php echo (intval($u['id_rol']) === 2 ? 'selected' : ''); ?>>Operador</option>
		<option value="4" <?php echo (intval($u['id_rol']) === 4 ? 'selected' : ''); ?>>Propietario</option>
	</select>

	<label>Profesión (solo residente)</label>
	<input class="login-input" type="text" name="profesion" value="<?php echo htmlspecialchars($u['profesion']); ?>">

	<label>Número inmueble existente</label>
	<input class="login-input" type="number" name="num_inmueble" value="<?php echo htmlspecialchars($numero_inmueble); ?>" required>

	<label>Torre del inmueble existente</label>
	<select class="login-input" name="num_torre" required>
		<option value="">Seleccione</option>
		<option value="Alpha" <?php echo ($torre_inmueble === 'Alpha' ? 'selected' : ''); ?>>Alpha</option>
		<option value="Bravo" <?php echo ($torre_inmueble === 'Bravo' ? 'selected' : ''); ?>>Bravo</option>
		<option value="Charlie" <?php echo ($torre_inmueble === 'Charlie' ? 'selected' : ''); ?>>Charlie</option>
		<option value="Delta" <?php echo ($torre_inmueble === 'Delta' ? 'selected' : ''); ?>>Delta</option>
	</select>

	<label>Contacto emergencia - Nombre</label>
	<input class="login-input" type="text" name="nombre_emergencia" value="<?php echo htmlspecialchars($emergencia['nombre']); ?>">

	<label>Contacto emergencia - Teléfono</label>
	<input class="login-input" type="number" name="num_tlf_emergencia" value="<?php echo htmlspecialchars($emergencia['telefono']); ?>">

	<label>Contacto emergencia - Relación</label>
	<input class="login-input" type="text" name="relacion" value="<?php echo htmlspecialchars($emergencia['relacion']); ?>">

	<label>Mascota - Tipo</label>
	<input class="login-input" type="text" name="tipo_mascota" value="<?php echo htmlspecialchars($mascota['tipo']); ?>">

	<label>Mascota - Raza</label>
	<input class="login-input" type="text" name="raza" value="<?php echo htmlspecialchars($mascota['raza']); ?>">

	<label>Mascota - Cantidad</label>
	<input class="login-input" type="number" name="cantidad_mascota" value="<?php echo htmlspecialchars($mascota['cantidad']); ?>">

	<button class="login-btn" type="submit">Guardar cambios</button>
</form>
</div>
</main>
</body>
</html>

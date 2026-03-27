<?php
session_start();
include('../../programas/comun/conexion.php');

if (!isset($_SESSION['rol']) || intval($_SESSION['rol']) !== 4) {
    header('Location: ../comun/login.html');
    exit;
}

$id_propietario = intval($_SESSION['id_propietario'] ?? 0);
if ($id_propietario <= 0 && isset($_SESSION['id_persona'])) {
    $id_persona = intval($_SESSION['id_persona']);
    $q_prop = mysqli_query($conexion, "SELECT id_propietario FROM PROPIETARIOS WHERE id_persona=$id_persona LIMIT 1");
    if ($q_prop && mysqli_num_rows($q_prop) > 0) {
        $id_propietario = intval(mysqli_fetch_assoc($q_prop)['id_propietario']);
        $_SESSION['id_propietario'] = $id_propietario;
    }
}

$inmuebles = [];
if ($id_propietario > 0) {
    $sql = "SELECT i.id_inmueble, i.numero, COALESCE(t.nombre, 'Sin torre') AS torre
            FROM INMUEBLES i
            LEFT JOIN TORRES t ON t.id_torre = i.id_torre
            WHERE i.id_propietario = $id_propietario
            ORDER BY t.nombre, i.numero";
    $res = mysqli_query($conexion, $sql);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $inmuebles[] = $row;
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Crear Usuario — Den Den Box</title>

<link rel="shortcut icon" href="../../img/warzone.svg">
<link rel="stylesheet" href="../../css/style.css">

</head>

<body>

<header class="header">
<div class="container navbar">

<a class="brand" href="index_propietario.html">
<img src="../../img/logo.png">
<div>
<div class="title">Den Den Box</div>
<div class="subtitle">Propietario - crear usuario</div>
</div>
</a>

<nav class="nav-links">
<a href="index_propietario.html">Inicio</a>
<a href="placeholders/ver_comunicaciones_propietario.html">Comunicaciones</a>
<a href="placeholders/documentos_propietario.html">Documentos</a>
<a href="propietario_crear.php" class="is-active">Crear usuario</a>
<a href="../../programas/auth/logout.php" class="btn-login">Cerrar Sesion</a>
</nav>

</div>
</header>

<main class="container form-page">

<h1>Crear usuario</h1>
<p>Registrar un nuevo residente en uno de tus inmuebles.</p>

<div class="form-card">

<form action="../../programas/propietario/guardar_usuario_propietario.php" method="POST">

<h2>Persona responsable</h2>

<label>Nombre Completo</label>
<input type="text" name="nombre" required class="login-input">

<label>Tipo de documento</label>
<select name="documento" required class="login-input">
<option>CC</option>
<option>TI</option>
<option>CEDULA DE EXTRANJERIA</option>
<option>PASAPORTE</option>
</select>

<label>Numero de documento</label>
<input type="number" name="num_doc" required class="login-input">

<label>Numero de telefono</label>
<input type="number" name="num_tlf" required class="login-input">

<label>Edad</label>
<input type="number" name="edad" required class="login-input">

<h2>Datos del inmueble</h2>

<p class="form-note form-note-tight">Solo puedes registrar residentes en inmuebles vinculados a tu perfil de propietario.</p>

<label>ID de inmueble de tu propiedad</label>
<select name="id_inmueble" required class="login-input" <?php echo empty($inmuebles) ? 'disabled' : ''; ?>>
<?php if (empty($inmuebles)): ?>
<option value="">No tienes inmuebles vinculados</option>
<?php else: ?>
<option value="">Selecciona un inmueble</option>
<?php foreach ($inmuebles as $inmueble): ?>
<option value="<?php echo intval($inmueble['id_inmueble']); ?>">
<?php echo 'ID ' . intval($inmueble['id_inmueble']) . ' - Torre ' . htmlspecialchars($inmueble['torre']) . ' - Apto ' . htmlspecialchars($inmueble['numero']); ?>
</option>
<?php endforeach; ?>
<?php endif; ?>
</select>
<p class="form-note form-note-tight">
<?php echo empty($inmuebles) ? 'No tienes inmuebles asociados a tu perfil. Contacta al administrador.' : 'Selecciona uno de tus inmuebles registrados.'; ?>
</p>

<label>Número de parqueadero <small>(opcional)</small></label>
<input type="number" name="parqueadero" class="login-input">

<label>Área del inmueble <small>(opcional)</small></label>
<input type="number" name="area" class="login-input">

<label>Profesion</label>
<input type="text" name="profesion" required class="login-input">

<h2>Personas en el inmueble</h2>
<p class="form-note form-note-tight-lg">
    Registra hasta 3 adultos y 3 menores asociados al apartamento.
</p>

<label>Adultos</label>
<input type="text" name="adultos[]" placeholder="Nombre del adulto 1" class="login-input mb-8">
<input type="text" name="adultos[]" placeholder="Nombre del adulto 2" class="login-input mb-8">
<input type="text" name="adultos[]" placeholder="Nombre del adulto 3" class="login-input mb-14">

<label class="mt-4">Menores</label>
<input type="text" name="menores[]" placeholder="Nombre del menor 1" class="login-input mb-8">
<input type="text" name="menores[]" placeholder="Nombre del menor 2" class="login-input mb-8">
<input type="text" name="menores[]" placeholder="Nombre del menor 3" class="login-input mb-16">

<h2>Contacto de emergencia</h2>

<label>Nombre</label>
<input type="text" name="nombre_emergencia" required class="login-input">

<label>Telefono</label>
<input type="number" name="num_tlf_emergencia" required class="login-input">

<label>Relacion</label>
<input type="text" name="relacion" required class="login-input">

<h2>Mascota</h2>

<label>Tipo</label>
<input type="text" name="tipo_mascota" class="login-input">

<label>Raza</label>
<input type="text" name="raza" class="login-input">

<label>Cantidad</label>
<input type="number" name="cantidad_mascota" class="login-input">

<h2>Credenciales</h2>

<label>Correo</label>
<input type="email" name="correo" required class="login-input">

<label>Contraseña</label>
<input type="password" name="contrasena" required class="login-input">

<button type="submit" class="login-btn" <?php echo empty($inmuebles) ? 'disabled' : ''; ?>>
Registrar residente
</button>

</form>

</div>

</main>

<section class="footer">© 2025 Den Den Box — Proyecto SENA</section>

</body>
</html>

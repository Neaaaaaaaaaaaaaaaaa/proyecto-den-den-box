<?php
session_start();
include("../comun/conexion.php");

$rol = $_SESSION['rol'] ?? null;

// Compatibilidad con bases antiguas: agrega id_inmueble si aún no existe.
$check_col = mysqli_query($conexion, "SHOW COLUMNS FROM COMUNICACIONES LIKE 'id_inmueble'");
if($check_col && mysqli_num_rows($check_col) === 0){
    mysqli_query($conexion, "ALTER TABLE COMUNICACIONES ADD COLUMN id_inmueble INT NULL");
}

// Solo Admin y Operador pueden acceder
if ($rol === null || ($rol != 1 && $rol != 2)) {
    header("Location: ../../html/comun/login.html");
    exit;
}

$historial = isset($_GET['historial']) && $_GET['historial'] === '1';

// ADMIN Y OPERADOR ven todas las comunicaciones
if ($historial) {
    $sql = "SELECT c.*, i.numero as numero_inmueble FROM COMUNICACIONES c LEFT JOIN INMUEBLES i ON c.id_inmueble = i.id_inmueble ORDER BY c.fecha DESC";
} else {
    $sql = "SELECT c.*, i.numero as numero_inmueble FROM COMUNICACIONES c LEFT JOIN INMUEBLES i ON c.id_inmueble = i.id_inmueble WHERE c.estado IN ('Activa','Vigente','Prioritario') ORDER BY c.fecha DESC";
}

$resultado = mysqli_query($conexion, $sql);
?>

<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Comunicaciones - Admin</title>
<link rel="stylesheet" href="../../css/style.css">
</head>

<body>

<div class="container">

<h1>Comunicaciones</h1>

<div style="margin-bottom:16px;">


    <?php if ($historial): ?>
        <a href="listar_comunicaciones_admin.php" class="buttonplace">Ver sólo vigentes</a>
    <?php else: ?>
        <a href="listar_comunicaciones_admin.php?historial=1" class="buttonplace">Ver historial completo</a>
    <?php endif; ?>
</div>

<div class="cards">

<?php if (mysqli_num_rows($resultado) === 0): ?>
    <div class="card">No hay comunicaciones disponibles.</div>
<?php else: ?>
    <?php while ($row = mysqli_fetch_assoc($resultado)): ?>
        <div class="card">
            <strong><?php echo $row['titulo']; ?></strong><br>
            Publicado: <?php echo $row['fecha']; ?><br>
            Tipo: <?php echo $row['tipo']; ?><br>
            <?php if (is_null($row['id_inmueble'])): ?>
                <span style="background-color:#4CAF50;color:white;padding:4px 8px;border-radius:4px;font-size:12px;">Comunicación Global</span>
            <?php else: ?>
                <span style="background-color:#2196F3;color:white;padding:4px 8px;border-radius:4px;font-size:12px;">Inmueble <?php echo $row['numero_inmueble']; ?></span>
            <?php endif; ?>
            <br>
            Estado:
            <span class="tag <?php
                if ($row['estado'] == "Activa") echo "tag-activa";
                if ($row['estado'] == "Vigente") echo "tag-vigente";
                if ($row['estado'] == "Prioritario") echo "tag-prioritario";
                if ($row['estado'] == "Inactiva") echo "tag-inactiva";
            ?>">
                <?php echo $row['estado']; ?>
            </span>
            <p><?php echo $row['contenido']; ?></p>
        </div>
    <?php endwhile; ?>
<?php endif; ?>

</div>

<br>

<a href="../../html/admin/placeholders/comunicaciones.html" class="buttonplace">Volver</a>

</div>

</body>
</html>

<?php
session_start();
include("../comun/conexion.php");

$rol = $_SESSION['rol'] ?? null;
$id_persona = $_SESSION['id_persona'] ?? null;

// Compatibilidad con bases antiguas: agrega id_inmueble si aún no existe.
$check_col = mysqli_query($conexion, "SHOW COLUMNS FROM COMUNICACIONES LIKE 'id_inmueble'");
if($check_col && mysqli_num_rows($check_col) === 0){
    mysqli_query($conexion, "ALTER TABLE COMUNICACIONES ADD COLUMN id_inmueble INT NULL");
}

// Compatibilidad con bases antiguas: agrega url_archivo si aún no existe.
$check_file_col = mysqli_query($conexion, "SHOW COLUMNS FROM COMUNICACIONES LIKE 'url_archivo'");
if($check_file_col && mysqli_num_rows($check_file_col) === 0){
    mysqli_query($conexion, "ALTER TABLE COMUNICACIONES ADD COLUMN url_archivo VARCHAR(255) NULL");
}

// Solo Residente y Propietario pueden acceder
if ($rol === null || ($rol != 3 && $rol != 4)) {
    header("Location: ../../html/comun/login.html?error=sesion_expirada");
    exit;
}

$historial = isset($_GET['historial']) && $_GET['historial'] === '1';
$embed = isset($_GET['embed']) && $_GET['embed'] === '1';

// Obtener los inmuebles del usuario
if ($rol == 3) {
    // Para residentes: obtener inmuebles desde RESIDENTE_INMUEBLE
    $sql_inmuebles = "SELECT DISTINCT i.id_inmueble FROM INMUEBLES i 
                     JOIN RESIDENTE_INMUEBLE ri ON i.id_inmueble = ri.id_inmueble 
                     JOIN RESIDENTES r ON ri.id_residente = r.id_residente 
                     WHERE r.id_persona='$id_persona'";
} else if ($rol == 4) {
    // Para propietarios: obtener inmuebles donde es propietario
    $sql_inmuebles = "SELECT id_inmueble FROM INMUEBLES WHERE id_propietario = (SELECT id_propietario FROM PROPIETARIOS WHERE id_persona='$id_persona')";
}

$resultado_inmuebles = mysqli_query($conexion, $sql_inmuebles);
$ids_inmuebles = [];

while ($row = mysqli_fetch_assoc($resultado_inmuebles)) {
    $ids_inmuebles[] = $row['id_inmueble'];
}

// Crear query para mostrar comunicaciones globales o especificadas para sus inmuebles
if (count($ids_inmuebles) > 0) {
    $ids_string = implode(',', $ids_inmuebles);
    if ($historial) {
        $sql = "SELECT c.*, i.numero as numero_inmueble FROM COMUNICACIONES c 
               LEFT JOIN INMUEBLES i ON c.id_inmueble = i.id_inmueble 
               WHERE c.id_inmueble IS NULL OR c.id_inmueble IN ($ids_string) 
               ORDER BY c.fecha DESC";
    } else {
        $sql = "SELECT c.*, i.numero as numero_inmueble FROM COMUNICACIONES c 
               LEFT JOIN INMUEBLES i ON c.id_inmueble = i.id_inmueble 
               WHERE (c.id_inmueble IS NULL OR c.id_inmueble IN ($ids_string)) 
               AND c.estado IN ('Activa','Vigente','Prioritario') 
               ORDER BY c.fecha DESC";
    }
} else {
    // Si no tiene inmuebles, solo mostrar comunicaciones globales
    if ($historial) {
        $sql = "SELECT c.*, i.numero as numero_inmueble FROM COMUNICACIONES c 
               LEFT JOIN INMUEBLES i ON c.id_inmueble = i.id_inmueble 
               WHERE c.id_inmueble IS NULL 
               ORDER BY c.fecha DESC";
    } else {
        $sql = "SELECT c.*, i.numero as numero_inmueble FROM COMUNICACIONES c 
               LEFT JOIN INMUEBLES i ON c.id_inmueble = i.id_inmueble 
               WHERE c.id_inmueble IS NULL 
               AND c.estado IN ('Activa','Vigente','Prioritario') 
               ORDER BY c.fecha DESC";
    }
}

$resultado = mysqli_query($conexion, $sql);
?>

<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Comunicaciones</title>
<link rel="stylesheet" href="../../css/style.css">
</head>

<body>

<div class="container">

<h1>Comunicaciones</h1>

<?php if (!$embed): ?>
<div style="margin-bottom:16px;">
    <?php if ($historial): ?>
        <a href="listar_comunicaciones_residente.php" class="buttonplace">Ver sólo vigentes</a>
    <?php else: ?>
        <a href="listar_comunicaciones_residente.php?historial=1" class="buttonplace">Ver historial completo</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="cards">

<?php if (mysqli_num_rows($resultado) === 0): ?>
    <div class="card">No hay comunicaciones disponibles.</div>
<?php else: ?>
    <?php while ($row = mysqli_fetch_assoc($resultado)): ?>
        <div class="card">
            <?php if (!empty($row['url_archivo'])): ?>
                <a href="../<?php echo htmlspecialchars($row['url_archivo']); ?>" target="_blank" style="font-weight:700;text-decoration:none;color:inherit;">
                    <?php echo htmlspecialchars($row['titulo']); ?>
                </a><br>
            <?php else: ?>
                <strong><?php echo htmlspecialchars($row['titulo']); ?></strong><br>
            <?php endif; ?>
            Publicado: <?php echo $row['fecha']; ?><br>
            Tipo: <?php echo htmlspecialchars($row['tipo']); ?><br>
            <?php if (is_null($row['id_inmueble'])): ?>
                <span style="background-color:#4CAF50;color:white;padding:4px 8px;border-radius:4px;font-size:12px;">Comunicación Global</span>
            <?php else: ?>
                <span style="background-color:#2196F3;color:white;padding:4px 8px;border-radius:4px;font-size:12px;">Inmueble <?php echo htmlspecialchars($row['numero_inmueble']); ?></span>
            <?php endif; ?>
            <br>
            Estado:
            <span class="tag <?php
                if ($row['estado'] == "Activa") echo "tag-activa";
                if ($row['estado'] == "Vigente") echo "tag-vigente";
                if ($row['estado'] == "Prioritario") echo "tag-prioritario";
                if ($row['estado'] == "Inactiva") echo "tag-inactiva";
            ?>">
                <?php echo htmlspecialchars($row['estado']); ?>
            </span>
            <p><?php echo nl2br(htmlspecialchars($row['contenido'])); ?></p>
            <?php if (!empty($row['url_archivo'])): ?>
                <a href="../<?php echo htmlspecialchars($row['url_archivo']); ?>" target="_blank" class="buttonplace" style="margin-top:8px;">Ver archivo adjunto</a>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
<?php endif; ?>

</div>

<?php if (!$embed): ?>
<br>
<a href="../../html/residente/placeholders/ver_comunicaciones.html" class="buttonplace">Volver</a>
<?php endif; ?>

</div>

</body>
</html>

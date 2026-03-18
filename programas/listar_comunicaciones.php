<?php
session_start();
include("conexion.php");

$rol = $_SESSION['rol'] ?? null;
$historial = isset($_GET['historial']) && $_GET['historial'] === '1';

if ($rol === null) {
    header("Location: ../html/login.html");
    exit;
}

if ($rol == 3 || $rol == 4) {
    if ($historial) {
        $sql = "SELECT * FROM COMUNICACIONES ORDER BY fecha DESC";
    } else {
        $sql = "SELECT * FROM COMUNICACIONES WHERE estado IN ('Activa','Vigente','Prioritario') ORDER BY fecha DESC";
    }
} else {
    $sql = "SELECT * FROM COMUNICACIONES ORDER BY fecha DESC";
}

$resultado = mysqli_query($conexion, $sql);
?>

<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Comunicaciones</title>
<link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="container">

<h1>Comunicaciones</h1>

<div style="margin-bottom:16px;">
    <?php if ($rol == 1 || $rol == 2): ?>
        <a href="../html/placeholders/comunicaciones.html" class="buttonplace">Publicar nueva comunicación</a>
    <?php endif; ?>

    <?php if ($rol == 3 || $rol == 4): ?>
        <?php if ($historial): ?>
            <a href="listar_comunicaciones.php" class="buttonplace">Ver sólo vigentes</a>
        <?php else: ?>
            <a href="listar_comunicaciones.php?historial=1" class="buttonplace">Ver historial completo</a>
        <?php endif; ?>
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

<a href="<?php echo ($rol == 3 || $rol == 4) ? '../html/placeholders/ver_comunicaciones.html' : '../html/placeholders/comunicaciones.html'; ?>" class="buttonplace">Volver</a>

</div>

</body>
</html>
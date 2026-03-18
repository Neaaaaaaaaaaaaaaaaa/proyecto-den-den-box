<?php
include("conexion.php");

$sql = "SELECT * FROM COMUNICACIONES ORDER BY fecha DESC";
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

<div class="cards">

<?php while($row = mysqli_fetch_assoc($resultado)){ ?>

<div class="card">

<strong><?php echo $row['titulo']; ?></strong><br>
Publicado: <?php echo $row['fecha']; ?><br>
Tipo: <?php echo $row['tipo']; ?><br>

Estado:
<span class="tag 
<?php 
if($row['estado']=="Activa") echo "tag-activa";
if($row['estado']=="Vigente") echo "tag-vigente";
if($row['estado']=="Prioritario") echo "tag-prioritario";
if($row['estado']=="Inactiva") echo "tag-inactiva";
?>
">
<?php echo $row['estado']; ?>
</span>

<p><?php echo $row['contenido']; ?></p>

</div>

<?php } ?>

</div>

<br>

<a href="../html/admin/comunicaciones.html" class="buttonplace">Volver</a>

</div>

</body>
</html>
<?php
$total = $_POST['total_personas'];
?>

<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Completar personas</title>
</head>

<body>

<h2>Ingrese los datos de las personas</h2>

<form action="guardar_usuario.php" method="POST">

<input type="hidden" name="total_personas" value="<?php echo $total; ?>">

<?php
for($i = 0; $i < $total; $i++) {
?>

<div style="background:#f9fafb;padding:10px;margin-bottom:10px;border-radius:8px;">

<label>Nombre persona <?php echo $i+1; ?></label>
<input type="text" name="personas[<?php echo $i; ?>][nombre]" required style="width:100%;margin-bottom:8px;">

<label>Edad</label>
<input type="number" name="personas[<?php echo $i; ?>][edad]" required style="width:100%;margin-bottom:8px;">

</div>

<?php } ?>

<button type="submit">Guardar todo</button>

</form>

</body>
</html>
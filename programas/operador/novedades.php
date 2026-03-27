<?php

include("../comun/conexion.php");

$nombre = $_POST['nombre del destinatario'];
$novedad = $_POST['tipo de novedad'];
$observacion = $_POST['observaciones'];

$sql = "INSERT INTO contactos(nombre,correo,mensaje)
		VALUES('$nombre','$novedad','$observacion')";

$resultado = mysqli_query($conexion,$sql);

if($resultado){
	echo "Novedad enviada correctamente";
}else{
	echo "Error al enviar la novedad";
}

?>

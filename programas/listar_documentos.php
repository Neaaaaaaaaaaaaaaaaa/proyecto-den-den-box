<?php

include("conexion.php");

$sql = "SELECT * FROM DOCUMENTOS ORDER BY fecha_subida DESC";

$resultado = mysqli_query($conexion,$sql);

while($fila = mysqli_fetch_assoc($resultado)){

echo "<div style='background:white;padding:15px;margin:10px;border-radius:10px;'>";

echo "<strong>".$fila['tipo_documento']."</strong><br><br>";

echo "<a href='../../documentos/".$fila['url_documento']. "' target='_blank'>Ver documento </a><br><br>";

echo "Fecha: ".$fila['fecha_subida'];

echo "</div>";

}

?>
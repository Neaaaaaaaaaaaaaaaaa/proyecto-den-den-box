<?php

include("conexion.php");

$nombre = $_POST['nombre'];
$correo = $_POST['correo'];
$mensaje = $_POST['mensaje'];

$sql = "INSERT INTO contactos(nombre,correo,mensaje)
        VALUES('$nombre','$correo','$mensaje')";

$resultado = mysqli_query($conexion,$sql);

if($resultado){
    echo "Mensaje enviado correctamente";
}else{
    echo "Error al enviar el mensaje";
}

?>

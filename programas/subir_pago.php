<?php
include("conexion.php");

if(isset($_POST['nombre'], $_POST['descripcion'], $_FILES['archivo'])){

    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];

    $archivo = $_FILES['archivo']['name'];
    $ruta = "../uploads/" . $archivo;

    move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta);

    $sql = "INSERT INTO pagos(nombre, descripcion, archivo)
            VALUES('$nombre','$descripcion','$archivo')";

    if(mysqli_query($conexion, $sql)){
        echo "Pago subido correctamente";
    } else {
        echo "Error en la base de datos";
    }

} else {
    echo "Faltan datos";
}
?>
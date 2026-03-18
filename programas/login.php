
<?php

session_start();
include("conexion.php");

$usuario = $_POST['usuario'];
$contrasena = $_POST['contrasena'];

$sql = "SELECT * 
        FROM usuarios u
        INNER JOIN personas p ON u.id_persona = p.id_persona
        WHERE p.correo = '$usuario' 
        AND u.contraseña = '$contrasena'";

$resultado = mysqli_query($conexion,$sql);

if(mysqli_num_rows($resultado) > 0){

    $datos = mysqli_fetch_assoc($resultado);

    $_SESSION['correo'] = $datos['correo'];
    $_SESSION['rol'] = $datos['id_rol'];

    if($datos['id_rol'] == 1){
    header("Location: ../html/index_admin.html");
    }

    elseif($datos['id_rol'] == 2){
        header("Location: ../html/index_operador.html");
    }

    elseif($datos['id_rol'] == 3){
        header("Location: ../html/index_residente.html");
    }

    elseif($datos['id_rol'] == 4){
        header("Location: ../html/index_propietario.html");
    }

}
else{
    echo "Usuario o contraseña incorrectos";
}

?>
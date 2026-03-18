
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
    $_SESSION['id_persona'] = $datos['id_persona'];
    $_SESSION['id_usuario'] = $datos['id_usuario'];

    if($datos['id_rol'] == 3){
        // Residente
        $sql_residente = "SELECT id_residente FROM RESIDENTES WHERE id_persona = {$datos['id_persona']}";
        $r_res = mysqli_query($conexion, $sql_residente);
        if(mysqli_num_rows($r_res) > 0){
            $fila_res = mysqli_fetch_assoc($r_res);
            $_SESSION['id_residente'] = $fila_res['id_residente'];
        }
    }

    if($datos['id_rol'] == 4){
        // Propietario
        $sql_propietario = "SELECT id_propietario FROM PROPIETARIOS WHERE id_persona = {$datos['id_persona']}";
        $r_prop = mysqli_query($conexion, $sql_propietario);
        if(mysqli_num_rows($r_prop) > 0){
            $fila_prop = mysqli_fetch_assoc($r_prop);
            $_SESSION['id_propietario'] = $fila_prop['id_propietario'];
        }
    }

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
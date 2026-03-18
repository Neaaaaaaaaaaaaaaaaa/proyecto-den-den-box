<?php
include("conexion.php");

$sql = "SELECT 
        t.nombre AS torre,
        i.numero AS apartamento,
        p_prop.nombre_completo AS propietario,
        GROUP_CONCAT(DISTINCT p_res.nombre_completo SEPARATOR ', ') AS residentes,
        GROUP_CONCAT(DISTINCT p_res.telefono SEPARATOR ', ') AS contactos,
        COUNT(DISTINCT r.id_residente) AS total_residentes,
        SUM(CASE WHEN p_res.edad >= 18 THEN 1 ELSE 0 END) AS adultos,
        SUM(CASE WHEN p_res.edad < 18 THEN 1 ELSE 0 END) AS menores
    FROM INMUEBLES i
    INNER JOIN TORRES t ON i.id_torre = t.id_torre
    LEFT JOIN PROPIETARIOS prop ON i.id_propietario = prop.id_propietario
    LEFT JOIN PERSONAS p_prop ON prop.id_persona = p_prop.id_persona
    LEFT JOIN RESIDENTE_INMUEBLE ri ON i.id_inmueble = ri.id_inmueble
    LEFT JOIN RESIDENTES r ON ri.id_residente = r.id_residente
    LEFT JOIN PERSONAS p_res ON r.id_persona = p_res.id_persona
    GROUP BY t.nombre, i.numero, p_prop.nombre_completo";

$resultado = mysqli_query($conexion, $sql);
?>

<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Tabla de Inmuebles</title>

<link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="container">

<div class="table-container">
<table class="residentes-table">

<thead>
<tr>
<th>Torre</th>
<th>Apartamento</th>
<th>Propietario</th>
<th>Residentes</th>
<th>Contacto</th>
<th>Total Personas</th>
<th>Adultos</th>
<th>Menores</th>
<th>Estado</th>
</tr>
</thead>

<tbody>

<?php while($fila = mysqli_fetch_assoc($resultado)){
$ocupado = ($fila['total_residentes'] > 0);
?>

<tr>
<td><?php echo $fila['torre']; ?></td>
<td><?php echo $fila['apartamento']; ?></td>
<td><?php echo $fila['propietario']; ?></td>
<td><?php echo $fila['residentes'] ?: '—'; ?></td>
<td><?php echo $fila['contactos'] ?: '—'; ?></td>
<td><?php echo $fila['total_residentes']; ?></td>
<td><?php echo $fila['adultos']; ?></td>
<td><?php echo $fila['menores']; ?></td>

<td>
<span class="<?php echo $ocupado ? 'ocupado' : 'desocupado'; ?>">
<?php echo $ocupado ? 'Ocupado' : 'Desocupado'; ?>
</span>
</td>
</tr>

<?php } ?>

</tbody>

</table>
</div>

</div>

</body>
</html>
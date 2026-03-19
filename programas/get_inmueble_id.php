<?php
session_start();
echo $_SESSION['id_inmueble'] ?? 'No disponible';
?>
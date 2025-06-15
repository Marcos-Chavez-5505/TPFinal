<?php
require_once 'conexion.php';

try {
    $pdo = conectarBD();
    echo "¡Conexión exitosa a la base de datos!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

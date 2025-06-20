<?php

// Crea una conexion con la base de datos
function conectarBD() {
    $host = 'localhost';
    $dbname = 'bdviajes';
    $usuario = 'root';
    $contrasena = ''; 

    $pdo = null;

    // PDO es una libreria que permite conectarse a la base de datos y ejecutar consultas SQL
    try {
        //Esta linea crea la conexion a la base de datos con los atributos ya creados
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $usuario, $contrasena);

        //Esto configura la conexión para que, si hay un error, PDO lance una excepción (throw Exception).
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    } catch (PDOException $e) {
        error_log("Error de conexión: " . $e->getMessage());
    }

    return $pdo;
}

// $conexion = conectarBD();

// if ($conexion) {
//     echo "Conexión exitosa<br>";
// } else {
//     echo "No se pudo conectar a la base de datos<br>";
// }
?>
<?php

// Crea una conexion con la base de datos
function conectarBD() {
    $host = 'localhost';
    $dbname = 'bdviajes';
    $usuario = 'root';
    $contrasena = ''; // cambiar los datos segun tu base, olvide los mios xd

    
    // PDO es una libreria que permite conectarse a la base de datos y ejecutar consultas SQL
    try {

        //Esta linea crea la conexion a la base de datos con los atributos ya creados
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $usuario, $contrasena);

        //Esto configura la conexión para que, si hay un error, PDO lance una excepción (throw Exception).
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;

    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}
?>
<?php
require_once 'conexion.php';

class Empresa {
    private $pdo;

    public function __construct() {
        //$pdo va a almacenar el objeto de conexión a la base de datos que ya creamos
        $this->pdo = conectarBD();
    }

    public function insertar($nombre, $direccion) {
        
        // Almacena el codigo SQL, los "?" son una forma de insertar un valor luego con mayor seguridad
        $sql = "INSERT INTO empresa (enombre, edireccion) VALUES (?, ?)";
        
        // Prepara la consulta para ser ejecutada
        $stmt = $this->pdo->prepare($sql);
        
        // execute([$nombre, $direccion]) ejecuta la consulta pasando los valores que reemplazan los ?
        return $stmt->execute([$nombre, $direccion]);
    }

    public function modificar($id, $nuevoNombre, $nuevaDireccion) {
        $sql = "UPDATE empresa SET enombre = ?, edireccion = ? WHERE idempresa = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$nuevoNombre, $nuevaDireccion, $id]);
    }

    public function eliminar($id) {
        $sql = "DELETE FROM empresa WHERE idempresa = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
}
?>

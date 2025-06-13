<?php
require_once 'conexion.php';

class ResponsableV {
    private $pdo;

    public function __construct() {
        $this->pdo = conectarBD();
    }

    public function insertar($licencia, $nombre, $apellido) {
        $sql = "INSERT INTO responsable (rnumerolicencia, rnombre, rapellido)
                VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$licencia, $nombre, $apellido]);
    }

    public function modificar($numeroEmpleado, $licencia, $nombre, $apellido) {
        $sql = "UPDATE responsable SET rnumerolicencia = ?, rnombre = ?, rapellido = ?
                WHERE rnumeroempleado = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$licencia, $nombre, $apellido, $numeroEmpleado]);
    }

    public function eliminar($numeroEmpleado) {
        $sql = "DELETE FROM responsable WHERE rnumeroempleado = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$numeroEmpleado]);
    }
}
?>

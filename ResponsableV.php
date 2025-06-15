<?php
require_once 'conexion.php';

class ResponsableV {
    private $pdo;

    public function __construct() {
        $this->pdo = conectarBD();
    }

    public function insertar($licencia, $nombre, $apellido) {
        $sql = "INSERT INTO responsable (r_numerolicencia, r_nombre, r_apellido)
                VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$licencia, $nombre, $apellido]);
    }

    public function modificar($numeroEmpleado, $licencia, $nombre, $apellido) {
        $sql = "UPDATE responsable SET r_numerolicencia = ?, r_nombre = ?, r_apellido = ?
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

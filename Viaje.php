<?php
require_once 'conexion.php';

class Viaje {
    private $pdo;

    public function __construct() {
        $this->pdo = conectarBD();
    }

    public function insertar($destino, $cantMax, $importe, $idempresa, $rnumeroempleado) {
        $sql = "INSERT INTO viaje (vdestino, vcantmaxpasajeros, vimporte, idempresa, rnumeroempleado)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$destino, $cantMax, $importe, $idempresa, $rnumeroempleado]);
    }

    public function modificar($idviaje, $destino, $cantMax, $importe, $idempresa, $rnumeroempleado) {
        $sql = "UPDATE viaje SET vdestino = ?, vcantmaxpasajeros = ?, vimporte = ?, 
                idempresa = ?, rnumeroempleado = ? WHERE idviaje = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$destino, $cantMax, $importe, $idempresa, $rnumeroempleado, $idviaje]);
    }

    public function eliminar($idviaje) {
        $sql = "DELETE FROM viaje WHERE idviaje = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$idviaje]);
    }
}
?>

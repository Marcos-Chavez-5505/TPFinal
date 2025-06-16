<?php
require_once 'conexion.php';

class ResponsableV {
    private $idResponsable; // r_numeroempleado (autoincrement)
    private $numLicencia;
    private $nombre;
    private $apellido;
    private $mensajeError;
    private $pdo;

    public function __construct() {
        $this->idResponsable = null;
        $this->numLicencia = 0;
        $this->nombre = "";
        $this->apellido = "";
        $this->mensajeError = "";
        $this->pdo = conectarBD();
    }

    // Getters
    public function getIdResponsable() { return $this->idResponsable; }
    public function getNumLicencia() { return $this->numLicencia; }
    public function getNombre() { return $this->nombre; }
    public function getApellido() { return $this->apellido; }
    public function getMensajeError() { return $this->mensajeError; }
    public function getPdo() { return $this->pdo; }

    // Setters
    public function setIdResponsable($idResponsable) { $this->idResponsable = $idResponsable; }
    public function setNumLicencia($numLicencia) { $this->numLicencia = $numLicencia; }
    public function setNombre($nombre) { $this->nombre = $nombre; }
    public function setApellido($apellido) { $this->apellido = $apellido; }
    public function setMensajeError($mensajeError) { $this->mensajeError = $mensajeError; }

    /** Carga numLicencia, nombre y apellido (sin id) */
    public function cargar($numLicencia, $nombre, $apellido) {
        $this->setNumLicencia($numLicencia);
        $this->setNombre($nombre);
        $this->setApellido($apellido);
    }

    public function insertar() {
        $resultado = false;
        $this->setMensajeError("");
        $sql = "INSERT INTO responsable (r_numerolicencia, r_nombre, r_apellido) VALUES (?, ?, ?)";
        try {
            $stmt = $this->getPdo()->prepare($sql);
            if ($stmt->execute([$this->getNumLicencia(), $this->getNombre(), $this->getApellido()])) {
                $this->setIdResponsable($this->getPdo()->lastInsertId());
                $resultado = true;
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al insertar Responsable: " . $e->getMessage());
        }
        return $resultado;
    }

    public function modificar() {
        $resultado = false;
        $this->setMensajeError("");
        $sql = "UPDATE responsable SET r_numerolicencia = ?, r_nombre = ?, r_apellido = ? WHERE r_numeroempleado = ?";
        try {
            $stmt = $this->getPdo()->prepare($sql);
            $resultado = $stmt->execute([
                $this->getNumLicencia(),
                $this->getNombre(),
                $this->getApellido(),
                $this->getIdResponsable()
            ]);
        } catch (PDOException $e) {
            $this->setMensajeError("Error al modificar Responsable: " . $e->getMessage());
        }
        return $resultado;
    }

    public function eliminar() {
        $resultado = false;
        $this->setMensajeError("");
        $sql = "DELETE FROM responsable WHERE r_numeroempleado = ?";
        try {
            $stmt = $this->getPdo()->prepare($sql);
            $resultado = $stmt->execute([$this->getIdResponsable()]);
        } catch (PDOException $e) {
            $this->setMensajeError("Error al eliminar Responsable: " . $e->getMessage());
        }
        return $resultado;
    }

    public function buscar($id) {
        $resultado = false;
        $this->setMensajeError("");
        $sql = "SELECT * FROM responsable WHERE r_numeroempleado = ?";
        try {
            $stmt = $this->getPdo()->prepare($sql);
            if ($stmt->execute([$id])) {
                if ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $this->setIdResponsable($fila['r_numeroempleado']);
                    $this->cargar($fila['r_numerolicencia'], $fila['r_nombre'], $fila['r_apellido']);
                    $resultado = true;
                }
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al buscar Responsable: " . $e->getMessage());
        }
        return $resultado;
    }

    public function listar($condicion = "") {
        $arreglo = null;
        $this->setMensajeError("");
        $sql = "SELECT * FROM responsable";
        if ($condicion != "") {
            $sql .= " WHERE " . $condicion;
        }
        $sql .= " ORDER BY r_apellido, r_nombre";

        try {
            $stmt = $this->getPdo()->query($sql);
            $arreglo = [];
            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $obj = new ResponsableV();
                $obj->setIdResponsable($fila['r_numeroempleado']);
                $obj->cargar($fila['r_numerolicencia'], $fila['r_nombre'], $fila['r_apellido']);
                array_push($arreglo, $obj);
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al listar Responsables: " . $e->getMessage());
        }
        return $arreglo;
    }

    public function __toString() {
        return "Responsable [ID: " . $this->getIdResponsable() . 
            ", Licencia: " . $this->getNumLicencia() . 
            ", Nombre: " . $this->getNombre() . 
            ", Apellido: " . $this->getApellido() . "]";
    }
}
?>

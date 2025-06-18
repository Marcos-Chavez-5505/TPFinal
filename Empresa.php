<?php
require_once 'conexion.php';

class Empresa {
    private $id_empresa;
    private $e_nombre;
    private $e_direccion;
    private $mensajeError;
    private $pdo;

    public function __construct() {
        $this->id_empresa = null; // null porque es autoincremental
        $this->e_nombre = "";
        $this->e_direccion = "";
        $this->mensajeError = "";
        $this->pdo = conectarBD();
    }

    // Getters
    public function getIdEmpresa() { return $this->id_empresa; }
    public function getNombre() { return $this->e_nombre; }
    public function getDireccion() { return $this->e_direccion; }
    public function getMensajeError() { return $this->mensajeError; }
    public function getPdo() { return $this->pdo; }

    // Setters
    public function setIdEmpresa($id_empresa) { $this->id_empresa = $id_empresa; }
    public function setNombre($e_nombre) { $this->e_nombre = $e_nombre; }
    public function setDireccion($e_direccion) { $this->e_direccion = $e_direccion; }
    public function setMensajeError($mensajeError) { $this->mensajeError = $mensajeError; }

    /** Carga nombre y dirección, sin id porque es autoincrement */
    public function cargar($nombre, $direccion) {
        $this->setNombre($nombre);
        $this->setDireccion($direccion);
    }

    public function insertar() {
        $resultado = false;
        $this->setMensajeError("");
        $sql = "INSERT INTO empresa (e_nombre, e_direccion) VALUES (?, ?)";
        try {
            $stmt = $this->getPdo()->prepare($sql);
            if ($stmt->execute([$this->getNombre(), $this->getDireccion()])) {
                $this->setIdEmpresa($this->getPdo()->lastInsertId());
                $resultado = true;
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al insertar Empresa: " . $e->getMessage());
        }
        return $resultado;
    }

    public function modificar() {
        $resultado = false;
        $this->setMensajeError("");
        $sql = "UPDATE empresa SET e_nombre = ?, e_direccion = ? WHERE id_empresa = ?";
        try {
            $stmt = $this->getPdo()->prepare($sql);
            $resultado = $stmt->execute([$this->getNombre(), $this->getDireccion(), $this->getIdEmpresa()]);
        } catch (PDOException $e) {
            $this->setMensajeError("Error al modificar Empresa: " . $e->getMessage());
        }
        return $resultado;
    }

    public function eliminar() {
        $resultado = false;
        $this->setMensajeError("");
        $sql = "DELETE FROM empresa WHERE id_empresa = ?";
        try {
            $stmt = $this->getPdo()->prepare($sql);
            $resultado = $stmt->execute([$this->getIdEmpresa()]);
        } catch (PDOException $e) {
            $this->setMensajeError("Error al eliminar Empresa: " . $e->getMessage());
        }
        return $resultado;
    }

    public function buscar($id) {
        $resultado = false;
        $this->setMensajeError("");
        $sql = "SELECT * FROM empresa WHERE id_empresa = ?";
        try {
            $stmt = $this->getPdo()->prepare($sql);
            if ($stmt->execute([$id])) {
                if ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $this->setIdEmpresa($fila['id_empresa']);
                    $this->cargar($fila['e_nombre'], $fila['e_direccion']);
                    $resultado = true;
                }
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al buscar Empresa: " . $e->getMessage());
        }
        return $resultado;
    }

    public function listar($condicion = "") {
        $arreglo = null;
        $this->setMensajeError("");
        $sql = "SELECT * FROM empresa";
        if ($condicion != "") {
            $sql .= " WHERE " . $condicion;
        }
        $sql .= " ORDER BY id_empresa";

        try {
            $stmt = $this->getPdo()->query($sql);
            $arreglo = [];
            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $obj = new Empresa();
                $obj->setIdEmpresa($fila['id_empresa']);
                $obj->cargar($fila['e_nombre'], $fila['e_direccion']);
                array_push($arreglo, $obj);
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al listar Empresas: " . $e->getMessage());
        }
        return $arreglo;
    }

    public function __toString() {
        return "Empresa [ID: " . $this->getIdEmpresa() . 
            ", Nombre: " . $this->getNombre() . 
            ", Dirección: " . $this->getDireccion() . "]";
    }

}
?>

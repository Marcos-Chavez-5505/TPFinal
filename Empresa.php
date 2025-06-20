<?php
require_once 'conexion.php';

class Empresa {
    private $id_empresa;
    private $e_nombre;
    private $e_direccion;
    private $activo; // Nuevo atributo para borrado lógico
    private $mensajeError;
    private $pdo;

    public function __construct() {
        $this->id_empresa = null;
        $this->e_nombre = "";
        $this->e_direccion = "";
        $this->activo = true; // Por defecto está activo
        $this->mensajeError = "";
        $this->pdo = conectarBD();
    }

    // Getters
    public function getIdEmpresa() { return $this->id_empresa; }
    public function getNombre() { return $this->e_nombre; }
    public function getDireccion() { return $this->e_direccion; }
    public function getActivo() { return $this->activo; }
    public function getMensajeError() { return $this->mensajeError; }
    public function getPdo() { return $this->pdo; }

    // Setters
    public function setIdEmpresa($id_empresa) { $this->id_empresa = $id_empresa; }
    public function setNombre($e_nombre) { $this->e_nombre = $e_nombre; }
    public function setDireccion($e_direccion) { $this->e_direccion = $e_direccion; }
    public function setActivo($activo) { $this->activo = $activo; }
    public function setMensajeError($mensajeError) { $this->mensajeError = $mensajeError; }

    /** Carga nombre y dirección, sin id porque es autoincrement */
    public function cargar($nombre, $direccion) {
        $this->setNombre($nombre);
        $this->setDireccion($direccion);
        $this->setActivo(true); // Siempre activo al cargar
    }

    public function insertar() {
        $resultado = false;
        $this->setMensajeError("");
        $sql = "INSERT INTO empresa (e_nombre, e_direccion, activo) VALUES (?, ?, ?)";
        try {
            $stmt = $this->getPdo()->prepare($sql);
            if ($stmt->execute([$this->getNombre(), $this->getDireccion(), $this->getActivo()])) {
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
        $sql = "UPDATE empresa SET e_nombre = ?, e_direccion = ? WHERE id_empresa = ? AND activo = TRUE";
        try {
            $stmt = $this->getPdo()->prepare($sql);
            $resultado = $stmt->execute([$this->getNombre(), $this->getDireccion(), $this->getIdEmpresa()]);
            if ($stmt->rowCount() == 0) {
                $this->setMensajeError("No se puede modificar: empresa no existe o está eliminada");
                $resultado = false;
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al modificar Empresa: " . $e->getMessage());
        }
        return $resultado;
    }

    /**
     * Borrado lógico: marca la empresa como inactiva
     * Valida que no tenga viajes activos antes de eliminar
     */
    public function eliminar() {
        $resultado = false;
        $this->setMensajeError("");
        
        // Verificar si la empresa tiene viajes activos
        if ($this->tieneViajesActivos()) {
            $this->setMensajeError("No se puede eliminar: la empresa tiene viajes activos");
            return false;
        }
        
        $sql = "UPDATE empresa SET activo = FALSE WHERE id_empresa = ? AND activo = TRUE";
        try {
            $stmt = $this->getPdo()->prepare($sql);
            $resultado = $stmt->execute([$this->getIdEmpresa()]);
            if ($stmt->rowCount() == 0) {
                $this->setMensajeError("No se puede eliminar: empresa no existe o ya está eliminada");
                $resultado = false;
            } else {
                $this->setActivo(false);
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al eliminar Empresa: " . $e->getMessage());
        }
        return $resultado;
    }

    /**
     * Reactivar una empresa previamente eliminada
     */
    public function reactivar() {
        $resultado = false;
        $this->setMensajeError("");
        $sql = "UPDATE empresa SET activo = TRUE WHERE id_empresa = ? AND activo = FALSE";
        try {
            $stmt = $this->getPdo()->prepare($sql);
            $resultado = $stmt->execute([$this->getIdEmpresa()]);
            if ($stmt->rowCount() == 0) {
                $this->setMensajeError("No se puede reactivar: empresa no existe o ya está activa");
                $resultado = false;
            } else {
                $this->setActivo(true);
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al reactivar Empresa: " . $e->getMessage());
        }
        return $resultado;
    }

    /**
     * Verifica si la empresa tiene viajes activos
     */
    private function tieneViajesActivos() {
        $sql = "SELECT COUNT(*) as total FROM viaje WHERE id_empresa = ? AND activo = TRUE";
        try {
            $stmt = $this->getPdo()->prepare($sql);
            $stmt->execute([$this->getIdEmpresa()]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['total'] > 0;
        } catch (PDOException $e) {
            return true; // En caso de error, asumir que tiene viajes para evitar eliminación
        }
    }

    public function buscar($id) {
        $resultado = false;
        $this->setMensajeError("");
        $sql = "SELECT * FROM empresa WHERE id_empresa = ? AND activo = TRUE";
        try {
            $stmt = $this->getPdo()->prepare($sql);
            if ($stmt->execute([$id])) {
                if ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $this->setIdEmpresa($fila['id_empresa']);
                    $this->cargar($fila['e_nombre'], $fila['e_direccion']);
                    $this->setActivo($fila['activo']);
                    $resultado = true;
                }
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al buscar Empresa: " . $e->getMessage());
        }
        return $resultado;
    }

    /**
     * Buscar empresa incluyendo las eliminadas (para reactivación)
     */
    public function buscarTodos($id) {
        $resultado = false;
        $this->setMensajeError("");
        $sql = "SELECT * FROM empresa WHERE id_empresa = ?";
        try {
            $stmt = $this->getPdo()->prepare($sql);
            if ($stmt->execute([$id])) {
                if ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $this->setIdEmpresa($fila['id_empresa']);
                    $this->setNombre($fila['e_nombre']);
                    $this->setDireccion($fila['e_direccion']);
                    $this->setActivo($fila['activo']);
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
        $sql = "SELECT * FROM empresa WHERE activo = TRUE";
        if ($condicion != "") {
            $sql .= " AND " . $condicion;
        }
        $sql .= " ORDER BY id_empresa";

        try {
            $stmt = $this->getPdo()->query($sql);
            $arreglo = [];
            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $obj = new Empresa();
                $obj->setIdEmpresa($fila['id_empresa']);
                $obj->cargar($fila['e_nombre'], $fila['e_direccion']);
                $obj->setActivo($fila['activo']);
                array_push($arreglo, $obj);
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al listar Empresas: " . $e->getMessage());
        }
        return $arreglo;
    }

    /**
     * Listar todas las empresas, incluyendo las eliminadas
     */
    public function listarTodos($condicion = "") {
        $arreglo = null;
        $this->setMensajeError("");
        $sql = "SELECT * FROM empresa";
        if ($condicion != "") {
            $sql .= " WHERE " . $condicion;
        }
        $sql .= " ORDER BY activo DESC, id_empresa";

        try {
            $stmt = $this->getPdo()->query($sql);
            $arreglo = [];
            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $obj = new Empresa();
                $obj->setIdEmpresa($fila['id_empresa']);
                $obj->setNombre($fila['e_nombre']);
                $obj->setDireccion($fila['e_direccion']);
                $obj->setActivo($fila['activo']);
                array_push($arreglo, $obj);
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al listar Empresas: " . $e->getMessage());
        }
        return $arreglo;
    }

    public function __toString() {
        $estado = $this->getActivo() ? "ACTIVA" : "ELIMINADA";
        return "Empresa [ID: " . $this->getIdEmpresa() . 
            ", Nombre: " . $this->getNombre() . 
            ", Dirección: " . $this->getDireccion() . 
            ", Estado: " . $estado . "]";
    }
}
?>
<?php
require_once 'conexion.php';
require_once 'Persona.php';

class ResponsableV extends Persona{
    private $idResponsable; // r_numeroempleado (autoincrement)
    private $numLicencia;
    private $pdo;
    private $mensajeError;

    public function __construct() {
        parent::__construct();
        $this->idResponsable = null;
        $this->numLicencia = 0;
        $this->pdo = conectarBD();
        $this->mensajeError = "";
    }

    // Getters y setters adicionales
    public function getIdResponsable() { return $this->idResponsable; }
    public function getNumLicencia() { return $this->numLicencia; }
    public function getMensajeError() { return $this->mensajeError; }
    public function getPdo() { return $this->pdo; }

    public function setIdResponsable($idResponsable) { $this->idResponsable = $idResponsable; }
    public function setNumLicencia($numLicencia) { $this->numLicencia = $numLicencia; }
    public function setMensajeError($msg) { $this->mensajeError = $msg; }

    public function cargar($numLicencia, $nombre, $apellido) {
        $this->setNumLicencia($numLicencia);
        $this->setNombre($nombre);
        $this->setApellido($apellido);
        $this->setActivo(true);
    }

    public function insertar() {
        $resultado = false;
        $this->setMensajeError("");
        $sql = "INSERT INTO responsable (r_numerolicencia, r_nombre, r_apellido, activo) VALUES (?, ?, ?, ?)";
        try {
            $stmt = $this->getPdo()->prepare($sql);
            if ($stmt->execute([$this->getNumLicencia(), $this->getNombre(), $this->getApellido(), $this->getActivo()])) {
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
        $sql = "UPDATE responsable SET r_numerolicencia = ?, r_nombre = ?, r_apellido = ? WHERE r_numeroempleado = ? AND activo = TRUE";
        try {
            $stmt = $this->getPdo()->prepare($sql);
            $resultado = $stmt->execute([
                $this->getNumLicencia(),
                $this->getNombre(),
                $this->getApellido(),
                $this->getIdResponsable()
            ]);
            if ($stmt->rowCount() == 0) {
                $this->setMensajeError("No se puede modificar: responsable no existe o está eliminado");
                $resultado = false;
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al modificar Responsable: " . $e->getMessage());
        }
        return $resultado;
    }

    /**
     * Borrado lógico: marca el responsable como inactivo
     * Valida que no tenga viajes activos antes de eliminar
     */
    public function eliminar() {
        $resultado = false;
        $this->setMensajeError("");
        
        // Verificar si el responsable tiene viajes activos
        if ($this->tieneViajesActivos()) {
            $this->setMensajeError("No se puede eliminar: el responsable tiene viajes activos");
            return false;
        }
        
        $sql = "UPDATE responsable SET activo = FALSE WHERE r_numeroempleado = ? AND activo = TRUE";
        try {
            $stmt = $this->getPdo()->prepare($sql);
            $resultado = $stmt->execute([$this->getIdResponsable()]);
            if ($stmt->rowCount() == 0) {
                $this->setMensajeError("No se puede eliminar: responsable no existe o ya está eliminado");
                $resultado = false;
            } else {
                $this->setActivo(false);
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al eliminar Responsable: " . $e->getMessage());
        }
        return $resultado;
    }

    /**
     * Reactivar un responsable previamente eliminado
     */
    public function reactivar() {
        $resultado = false;
        $this->setMensajeError("");
        $sql = "UPDATE responsable SET activo = TRUE WHERE r_numeroempleado = ? AND activo = FALSE";
        try {
            $stmt = $this->getPdo()->prepare($sql);
            $resultado = $stmt->execute([$this->getIdResponsable()]);
            if ($stmt->rowCount() == 0) {
                $this->setMensajeError("No se puede reactivar: responsable no existe o ya está activo");
                $resultado = false;
            } else {
                $this->setActivo(true);
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al reactivar Responsable: " . $e->getMessage());
        }
        return $resultado;
    }

    /**
     * Verifica si el responsable tiene viajes activos
     */
    private function tieneViajesActivos() {
        $sql = "SELECT COUNT(*) as total FROM viaje WHERE r_numeroempleado = ? AND activo = TRUE";
        try {
            $stmt = $this->getPdo()->prepare($sql);
            $stmt->execute([$this->getIdResponsable()]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['total'] > 0;
        } catch (PDOException $e) {
            return true; // En caso de error, asumir que tiene viajes para evitar eliminación
        }
    }

    public function buscar($id) {
        $resultado = false;
        $this->setMensajeError("");
        $sql = "SELECT * FROM responsable WHERE r_numeroempleado = ? AND activo = TRUE";
        try {
            $stmt = $this->getPdo()->prepare($sql);
            if ($stmt->execute([$id])) {
                if ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $this->setIdResponsable($fila['r_numeroempleado']);
                    $this->cargar($fila['r_numerolicencia'], $fila['r_nombre'], $fila['r_apellido']);
                    $this->setActivo($fila['activo']);
                    $resultado = true;
                }
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al buscar Responsable: " . $e->getMessage());
        }
        return $resultado;
    }

    /**
     * Buscar responsable incluyendo los eliminados (para reactivación)
     */
    public function buscarTodos($id) {
        $resultado = false;
        $this->setMensajeError("");
        $sql = "SELECT * FROM responsable WHERE r_numeroempleado = ?";
        try {
            $stmt = $this->getPdo()->prepare($sql);
            if ($stmt->execute([$id])) {
                if ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $this->setIdResponsable($fila['r_numeroempleado']);
                    $this->setNumLicencia($fila['r_numerolicencia']);
                    $this->setNombre($fila['r_nombre']);
                    $this->setApellido($fila['r_apellido']);
                    $this->setActivo($fila['activo']);
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
        $sql = "SELECT * FROM responsable WHERE activo = TRUE";
        if ($condicion != "") {
            $sql .= " AND " . $condicion;
        }
        $sql .= " ORDER BY r_apellido, r_nombre";

        try {
            $stmt = $this->getPdo()->query($sql);
            $arreglo = [];
            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $obj = new ResponsableV();
                $obj->setIdResponsable($fila['r_numeroempleado']);
                $obj->cargar($fila['r_numerolicencia'], $fila['r_nombre'], $fila['r_apellido']);
                $obj->setActivo($fila['activo']);
                array_push($arreglo, $obj);
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al listar Responsables: " . $e->getMessage());
        }
        return $arreglo;
    }

    /**
     * Listar todos los responsables, incluyendo los eliminados
     */
    public function listarTodos($condicion = "") {
        $arreglo = null;
        $this->setMensajeError("");
        $sql = "SELECT * FROM responsable";
        if ($condicion != "") {
            $sql .= " WHERE " . $condicion;
        }
        $sql .= " ORDER BY activo DESC, r_apellido, r_nombre";

        try {
            $stmt = $this->getPdo()->query($sql);
            $arreglo = [];
            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $obj = new ResponsableV();
                $obj->setIdResponsable($fila['r_numeroempleado']);
                $obj->setNumLicencia($fila['r_numerolicencia']);
                $obj->setNombre($fila['r_nombre']);
                $obj->setApellido($fila['r_apellido']);
                $obj->setActivo($fila['activo']);
                array_push($arreglo, $obj);
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al listar Responsables: " . $e->getMessage());
        }
        return $arreglo;
    }

    public function __toString() {
        $estado = $this->getActivo() ? "ACTIVO" : "ELIMINADO";
        return "Responsable [ID: " . $this->getIdResponsable() . 
            ", Licencia: " . $this->getNumLicencia() . 
            ", Nombre: " . $this->getNombre() . 
            ", Apellido: " . $this->getApellido() . 
            ", Estado: " . $estado . "]";
    }
}
?>
<?php
require_once 'conexion.php';

class Persona {
    protected $documento;
    protected $nombre;
    protected $apellido;
    protected $activo;
    protected $pdo;
    protected $mensajeError;

    public function __construct() {
        $this->documento = "";
        $this->nombre = "";
        $this->apellido = "";
        $this->activo = true;
        $this->pdo = conectarBD();
        $this->mensajeError = "";
    }

    // Getters
    public function getDocumento() { return $this->documento; }
    public function getNombre() { return $this->nombre; }
    public function getApellido() { return $this->apellido; }
    public function getActivo() { return $this->activo; }
    public function getMensajeError() { return $this->mensajeError; }
    public function getPdo() { return $this->pdo; }

    // Setters
    public function setDocumento($documento) { $this->documento = $documento; }
    public function setNombre($nombre) { $this->nombre = $nombre; }
    public function setApellido($apellido) { $this->apellido = $apellido; }
    public function setActivo($activo) { $this->activo = $activo; }
    public function setMensajeError($msg) { $this->mensajeError = $msg; }

    // Cargar datos
    public function cargar($documento, $nombre, $apellido, $activo = true) {
        $this->setDocumento($documento);
        $this->setNombre($nombre);
        $this->setApellido($apellido);
        $this->setActivo($activo);
    }

    // Insertar persona
    public function insertar() {
        $resultado = false;
        $this->setMensajeError("");
        $sql = "INSERT INTO persona (documento, nombre, apellido, activo) VALUES (?, ?, ?, ?)";
        try {
            $stmt = $this->getPdo()->prepare($sql);
            $resultado = $stmt->execute([
                $this->getDocumento(),
                $this->getNombre(),
                $this->getApellido(),
                $this->getActivo()
            ]);
        } catch (PDOException $e) {
            $this->setMensajeError("Error al insertar Persona: " . $e->getMessage());
        }
        return $resultado;
    }

    // Modificar persona
    public function modificar() {
        $resultado = false;
        $this->setMensajeError("");
        $sql = "UPDATE persona SET nombre = ?, apellido = ? WHERE documento = ? AND activo = TRUE";
        try {
            $stmt = $this->getPdo()->prepare($sql);
            $resultado = $stmt->execute([
                $this->getNombre(),
                $this->getApellido(),
                $this->getDocumento()
            ]);
            if ($stmt->rowCount() == 0) {
                $this->setMensajeError("No se puede modificar: persona no existe o está inactiva");
                $resultado = false;
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al modificar Persona: " . $e->getMessage());
        }
        return $resultado;
    }

    // Eliminar (desactivar) persona
    public function eliminar() {
        $resultado = false;
        $this->setMensajeError("");
        $sql = "UPDATE persona SET activo = FALSE WHERE documento = ? AND activo = TRUE";
        try {
            $stmt = $this->getPdo()->prepare($sql);
            $resultado = $stmt->execute([$this->getDocumento()]);
            if ($stmt->rowCount() == 0) {
                $this->setMensajeError("No se puede eliminar: persona no existe o ya está inactiva");
                $resultado = false;
            } else {
                $this->setActivo(false);
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al eliminar Persona: " . $e->getMessage());
        }
        return $resultado;
    }

    // Reactivar persona
    public function reactivar() {
        $resultado = false;
        $this->setMensajeError("");
        $sql = "UPDATE persona SET activo = TRUE WHERE documento = ? AND activo = FALSE";
        try {
            $stmt = $this->getPdo()->prepare($sql);
            $resultado = $stmt->execute([$this->getDocumento()]);
            if ($stmt->rowCount() == 0) {
                $this->setMensajeError("No se puede reactivar: persona no existe o ya está activa");
                $resultado = false;
            } else {
                $this->setActivo(true);
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al reactivar Persona: " . $e->getMessage());
        }
        return $resultado;
    }

    // Buscar persona por documento
    public function buscar($documento) {
        $resultado = false;
        $this->setMensajeError("");
        $sql = "SELECT * FROM persona WHERE documento = ?";
        try {
            $stmt = $this->getPdo()->prepare($sql);
            if ($stmt->execute([$documento])) {
                if ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $this->cargar($fila['documento'], $fila['nombre'], $fila['apellido'], $fila['activo']);
                    $resultado = true;
                }
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al buscar Persona: " . $e->getMessage());
        }
        return $resultado;
    }

    // Listar personas (activos por defecto)
    public function listar($condicion = "") {
        $arreglo = [];
        $this->setMensajeError("");
        $sql = "SELECT * FROM persona WHERE activo = TRUE";
        if ($condicion != "") {
            $sql .= " AND " . $condicion;
        }
        $sql .= " ORDER BY apellido, nombre";
        try {
            $stmt = $this->getPdo()->query($sql);
            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $obj = new Persona();
                $obj->cargar($fila['documento'], $fila['nombre'], $fila['apellido'], $fila['activo']);
                $arreglo[] = $obj;
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al listar Personas: " . $e->getMessage());
        }
        return $arreglo;
    }

    public function __toString() {
        $estado = $this->getActivo() ? "ACTIVO" : "INACTIVO";
        return "Persona [Documento: {$this->getDocumento()}, Nombre: {$this->getNombre()}, Apellido: {$this->getApellido()}, Estado: $estado]";
    }
}
?>

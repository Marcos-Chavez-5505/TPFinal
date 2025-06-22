<?php
require_once 'conexion.php';
require_once 'Persona.php';

class Pasajero extends Persona {
    private $telefono;
    protected $pdo;
    protected $mensajeError;

    public function __construct() {
        parent::__construct();
        $this->telefono = "";
        $this->pdo = conectarBD();
        $this->mensajeError = "";
    }

    public function getTelefono() { return $this->telefono; }
    public function getMensajeError() { return $this->mensajeError; }
    public function setTelefono($telefono) { $this->telefono = $telefono; }
    public function setMensajeError($msg) { $this->mensajeError = $msg; }

    public function cargar($documento, $nombre, $apellido, $telefono = "", $activo = true) {
        parent::cargar($documento, $nombre, $apellido, $activo);
        $this->setTelefono($telefono);
    }

    public function asignarComoPasajero($documento, $telefono) {
        $resultado = false;
        $this->setMensajeError("");
        
        try {
            $sqlVerificar = "SELECT documento FROM persona WHERE documento = ? AND activo = TRUE";
            $stmtVerificar = $this->pdo->prepare($sqlVerificar);
            $stmtVerificar->execute([$documento]);
            
            if ($stmtVerificar->rowCount() > 0) {
                $sqlInsert = "INSERT INTO pasajero (documento, p_telefono, activo) VALUES (?, ?, TRUE)";
                $stmtInsert = $this->pdo->prepare($sqlInsert);
                $resultado = $stmtInsert->execute([$documento, $telefono]);
                
                if (!$resultado) {
                    $this->setMensajeError("No se pudo asignar como pasajero");
                }
            } else {
                $this->setMensajeError("La persona no existe o está inactiva");
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al asignar pasajero: " . $e->getMessage());
        }
        
        return $resultado;
    }

    public function modificar() {
        $resultado = false;
        $this->setMensajeError("");
        
        try {
            $sqlPersona = "UPDATE persona SET nombre = ?, apellido = ? WHERE documento = ? AND activo = TRUE";
            $stmtPersona = $this->pdo->prepare($sqlPersona);
            $okPersona = $stmtPersona->execute([$this->getNombre(), $this->getApellido(), $this->getDocumento()]);
            
            if ($okPersona) {
                $sqlPasajero = "UPDATE pasajero SET p_telefono = ? WHERE documento = ?";
                $stmtPasajero = $this->pdo->prepare($sqlPasajero);
                $resultado = $stmtPasajero->execute([$this->telefono, $this->getDocumento()]);
                
                if (!$resultado) {
                    $this->setMensajeError("No se pudo actualizar el teléfono");
                }
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al modificar: " . $e->getMessage());
        }
        
        return $resultado;
    }

    public function eliminar() {
        $resultado = false;
        $this->setMensajeError("");
        
        try {
            $sql = "UPDATE persona SET activo = FALSE WHERE documento = ? AND activo = TRUE";
            $stmt = $this->pdo->prepare($sql);
            $resultado = $stmt->execute([$this->getDocumento()]);
            
            if ($stmt->rowCount() == 0) {
                $this->setMensajeError("Pasajero no existe o ya está eliminado");
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al eliminar: " . $e->getMessage());
        }
        
        return $resultado;
    }

    public function reactivar() {
        $resultado = false;
        $this->setMensajeError("");
        
        try {
            $sql = "UPDATE persona SET activo = TRUE WHERE documento = ? AND activo = FALSE";
            $stmt = $this->pdo->prepare($sql);
            $resultado = $stmt->execute([$this->getDocumento()]);
            
            if ($stmt->rowCount() == 0) {
                $this->setMensajeError("Pasajero no existe o ya está activo");
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al reactivar: " . $e->getMessage());
        }
        
        return $resultado;
    }

    public function buscar($documento) {
        $resultado = false;
        $this->setMensajeError("");
        
        try {
            $sql = "SELECT p.documento, p.nombre, p.apellido, p.activo, ps.p_telefono 
                    FROM persona p 
                    JOIN pasajero ps ON p.documento = ps.documento 
                    WHERE p.documento = ? AND p.activo = TRUE";
            $stmt = $this->pdo->prepare($sql);
            
            if ($stmt->execute([$documento])) {
                $fila = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($fila) {
                    $this->cargar($fila['documento'], $fila['nombre'], $fila['apellido'], $fila['p_telefono'], $fila['activo']);
                    $resultado = true;
                }
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al buscar: " . $e->getMessage());
        }
        
        return $resultado;
    }

    public function listar($condicion = "") {
        $arreglo = [];
        $this->setMensajeError("");
        
        try {
            $sql = "SELECT p.documento, p.nombre, p.apellido, p.activo, ps.p_telefono 
                    FROM persona p 
                    JOIN pasajero ps ON p.documento = ps.documento 
                    WHERE p.activo = TRUE";
            
            if (!empty($condicion)) {
                $sql .= " AND " . $condicion;
            }
            
            $sql .= " ORDER BY p.apellido, p.nombre";
            
            $stmt = $this->pdo->query($sql);
            
            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $obj = new Pasajero();
                $obj->cargar($fila['documento'], $fila['nombre'], $fila['apellido'], $fila['p_telefono'], $fila['activo']);
                $arreglo[] = $obj;
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al listar: " . $e->getMessage());
        }
        
        return $arreglo;
    }

    public function __toString() {
        return "Pasajero [Documento: " . $this->getDocumento() . 
               ", Nombre: " . $this->getNombre() . " " . $this->getApellido() . 
               ", Teléfono: " . $this->telefono . 
               ", Estado: " . ($this->getActivo() ? "ACTIVO" : "INACTIVO") . "]";
    }
}
?>
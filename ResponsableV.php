<?php
require_once 'conexion.php';
require_once 'Persona.php';

class ResponsableV extends Persona {
    private $numLicencia;
    protected $pdo;
    protected $mensajeError;

    public function __construct() {
        parent::__construct();
        $this->numLicencia = 0;
        $this->pdo = conectarBD();
        $this->mensajeError = "";
    }

    public function getNumLicencia() { return $this->numLicencia; }
    public function getMensajeError() { return $this->mensajeError; }
    public function setNumLicencia($numLicencia) { $this->numLicencia = $numLicencia; }
    public function setMensajeError($msg) { $this->mensajeError = $msg; }

    public function cargar($numLicencia, $nombre, $apellido, $documento = null) {
        $this->setNumLicencia($numLicencia);
        $this->setNombre($nombre);
        $this->setApellido($apellido);
        if ($documento !== null) { 
            $this->setDocumento($documento); 
        }
    }

    public function asignarComoResponsable($documento, $numLicencia) {
        $resultado = false;
        $this->setMensajeError("");
        
        try {
            $sql = "INSERT INTO responsable (documento, r_numerolicencia, activo) VALUES (?, ?, TRUE)";
            $stmt = $this->pdo->prepare($sql);
            $resultado = $stmt->execute([$documento, $numLicencia]);
            
            if (!$resultado) {
                $this->setMensajeError("No se pudo asignar como responsable");
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al asignar responsable: " . $e->getMessage());
        }
        
        return $resultado;
    }

    public function modificar() {
        $resultado = false;
        $this->setMensajeError("");
        
        try {
            $sql = "UPDATE responsable SET r_numerolicencia = ? WHERE documento = ?";
            $stmt = $this->pdo->prepare($sql);
            $resultado = $stmt->execute([$this->numLicencia, $this->getDocumento()]);
            
            if (!$resultado) {
                $this->setMensajeError("No se pudo modificar el responsable");
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
            $sql = "UPDATE responsable SET activo = FALSE WHERE documento = ?";
            $stmt = $this->pdo->prepare($sql);
            $resultado = $stmt->execute([$this->getDocumento()]);
            
            if (!$resultado) {
                $this->setMensajeError("No se pudo eliminar el responsable");
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al eliminar: " . $e->getMessage());
        }
        
        return $resultado;
    }

    public function buscar($documento) {
        $resultado = false;
        $this->setMensajeError("");
        
        try {
            $sql = "SELECT p.documento, p.nombre, p.apellido, r.r_numerolicencia 
                    FROM persona p 
                    JOIN responsable r ON p.documento = r.documento 
                    WHERE p.documento = ? AND p.activo = TRUE";
            $stmt = $this->pdo->prepare($sql);
            
            if ($stmt->execute([$documento])) {
                $fila = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($fila) {
                    $this->cargar($fila['r_numerolicencia'], $fila['nombre'], $fila['apellido'], $fila['documento']);
                    $resultado = true;
                }
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al buscar: " . $e->getMessage());
        }
        
        return $resultado;
    }

    public function listar($condicion = "") {
        $arreglo = array();
        $this->setMensajeError("");
        
        try {
            $sql = "SELECT p.documento, p.nombre, p.apellido, r.r_numerolicencia 
                    FROM persona p 
                    JOIN responsable r ON p.documento = r.documento 
                    WHERE p.activo = TRUE";
            
            if (!empty($condicion)) {
                $sql .= " AND " . $condicion;
            }
            
            $stmt = $this->pdo->query($sql);
            
            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $obj = new ResponsableV();
                $obj->cargar($fila['r_numerolicencia'], $fila['nombre'], $fila['apellido'], $fila['documento']);
                $arreglo[] = $obj;
            }
            
            $resultado = true;
        } catch (PDOException $e) {
            $this->setMensajeError("Error al listar: " . $e->getMessage());
        }
        
        return $arreglo;
    }

    public function reactivar() {
        $resultado = false;
        $this->setMensajeError("");
        
        $sql = "UPDATE responsable SET activo = TRUE WHERE documento = ? AND activo = FALSE";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $resultado = $stmt->execute([$this->getDocumento()]);
            
            if ($stmt->rowCount() == 0) {
                $this->setMensajeError("No se puede reactivar: el responsable no existe o ya está activo");
                $resultado = false;
            }
            
        } catch (PDOException $e) {
            $this->setMensajeError("Error al reactivar responsable: " . $e->getMessage());
        }
        
        return $resultado;
    }


    public function __toString() {
        return "Responsable [Licencia: " . $this->numLicencia . 
               ", Nombre: " . $this->getNombre() . 
               " " . $this->getApellido() . 
               ", Documento: " . $this->getDocumento() . "]";
    }
}
?>
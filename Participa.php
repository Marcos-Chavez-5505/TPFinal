<?php
require_once 'conexion.php';
require_once 'Viaje.php';
require_once 'Pasajero.php';

class Participa {
    private $idViaje;
    private $documento;
    private $activo;
    private $pdo;
    private $mensajeError;

    public function __construct() {
        $this->idViaje = null;
        $this->documento = "";
        $this->activo = true;
        $this->pdo = conectarBD();
        $this->mensajeError = "";
    }

    // Getters y Setters (se mantienen igual)
    public function getIdViaje() { return $this->idViaje; }
    public function getDocumento() { return $this->documento; }
    public function getActivo() { return $this->activo; }
    public function getMensajeError() { return $this->mensajeError; }

    public function setIdViaje($id) { $this->idViaje = $id; }
    public function setDocumento($doc) { $this->documento = $doc; }
    public function setActivo($activo) { $this->activo = $activo; }
    public function setMensajeError($msg) { $this->mensajeError = $msg; }

    public function insertar() {
        $resultado = false;
        $this->setMensajeError("");
        
        try {
            $sql = "INSERT INTO participa (id_viaje, documento, activo) VALUES (?, ?, TRUE)";
            $stmt = $this->pdo->prepare($sql);
            $resultado = $stmt->execute([$this->getIdViaje(), $this->getDocumento()]);
            
            if (!$resultado) {
                $this->setMensajeError("No se pudo insertar la participación");
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al insertar: " . $e->getMessage());
        }
        
        return $resultado;
    }

    public function eliminar() {
        $resultado = false;
        $this->setMensajeError("");
        
        try {
            $sql = "UPDATE participa SET activo = FALSE WHERE id_viaje = ? AND documento = ? AND activo = TRUE";
            $stmt = $this->pdo->prepare($sql);
            $resultado = $stmt->execute([$this->getIdViaje(), $this->getDocumento()]);
            
            if ($resultado) {
                $this->setActivo(false);
            } else {
                $this->setMensajeError("No se encontró participación activa para eliminar");
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al eliminar: " . $e->getMessage());
        }
        
        return $resultado;
    }

    public function buscar($idViaje, $documento, $soloActivos = true) {
        $resultado = false;
        $this->setMensajeError("");
        
        try {
            $sql = "SELECT * FROM participa WHERE id_viaje = ? AND documento = ?";
            if ($soloActivos) {
                $sql .= " AND activo = TRUE";
            }
            
            $stmt = $this->pdo->prepare($sql);
            if ($stmt->execute([$idViaje, $documento])) {
                $fila = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($fila) {
                    $this->setIdViaje($fila['id_viaje']);
                    $this->setDocumento($fila['documento']);
                    $this->setActivo($fila['activo']);
                    $resultado = true;
                }
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al buscar: " . $e->getMessage());
        }
        
        return $resultado;
    }

    public function listarPasajerosPorViaje($idViaje) {
        $lista = [];
        $this->setMensajeError("");
        
        try {
            $sql = "SELECT p.documento, p.nombre, p.apellido, ps.p_telefono as telefono 
                    FROM persona p
                    JOIN pasajero ps ON p.documento = ps.documento
                    JOIN participa pa ON p.documento = pa.documento
                    WHERE pa.id_viaje = ? AND pa.activo = TRUE 
                    AND p.activo = TRUE AND ps.activo = TRUE";
            
            $stmt = $this->pdo->prepare($sql);
            if ($stmt->execute([$idViaje])) {
                while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $pasajero = new Pasajero();
                    $pasajero->cargar(
                        $fila['documento'],
                        $fila['nombre'],
                        $fila['apellido'],
                        $fila['telefono']
                    );
                    $lista[] = $pasajero;
                }
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al listar pasajeros: " . $e->getMessage());
        }
        
        return $lista;
    }

    public function listarViajesPorPasajero($documento) {
        $lista = [];
        $this->setMensajeError("");
        
        try {
            $sql = "SELECT v.* FROM viaje v
                    INNER JOIN participa pa ON v.id_viaje = pa.id_viaje
                    WHERE pa.documento = ? AND pa.activo = TRUE AND v.activo = TRUE";
            
            $stmt = $this->pdo->prepare($sql);
            if ($stmt->execute([$documento])) {
                while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $viaje = new Viaje();
                    if ($viaje->buscar($fila['id_viaje'])) {
                        $lista[] = $viaje;
                    }
                }
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al listar viajes: " . $e->getMessage());
        }
        
        return $lista;
    }

    public function listarTodosPasajerosPorViaje($idViaje) {
        $lista = [];
        $this->setMensajeError("");

        try {
            $sql = "SELECT p.documento, p.nombre, p.apellido, ps.p_telefono as telefono 
                    FROM persona p
                    JOIN pasajero ps ON p.documento = ps.documento
                    JOIN participa pa ON p.documento = pa.documento
                    WHERE pa.id_viaje = ?";
            
            $stmt = $this->pdo->prepare($sql);
            if ($stmt->execute([$idViaje])) {
                while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $pasajero = new Pasajero();
                    $pasajero->cargar(
                        $fila['documento'],
                        $fila['nombre'],
                        $fila['apellido'],
                        $fila['telefono']
                    );
                    $lista[] = $pasajero;
                }
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al listar todos los pasajeros: " . $e->getMessage());
        }

        return $lista;
    }


    public function listar($condicion = "") {
        $participaciones = [];
        $this->setMensajeError("");
        
        try {
            $sql = "SELECT * FROM participa WHERE activo = TRUE";
            if (!empty($condicion)) {
                $sql .= " AND " . $condicion;
            }
            
            $stmt = $this->pdo->prepare($sql);
            if ($stmt->execute()) {
                while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $obj = new Participa();
                    $obj->setIdViaje($fila['id_viaje']);
                    $obj->setDocumento($fila['documento']);
                    $obj->setActivo($fila['activo']);
                    $participaciones[] = $obj;
                }
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al listar participaciones: " . $e->getMessage());
        }
        
        return $participaciones;
    }

    public function reactivar() {
        $resultado = false;
        $this->setMensajeError("");

        $sql = "UPDATE participa SET activo = TRUE WHERE id_viaje = ? AND documento = ? AND activo = FALSE";

        try {
            $stmt = $this->pdo->prepare($sql);
            $resultado = $stmt->execute([$this->getIdViaje(), $this->getDocumento()]);

            if ($stmt->rowCount() == 0) {
                $this->setMensajeError("No se puede reactivar: la participación no existe o ya está activa");
                $resultado = false;
            } else {
                $this->setActivo(true);
            }

        } catch (PDOException $e) {
            $this->setMensajeError("Error al reactivar participación: " . $e->getMessage());
        }

        return $resultado;
    }

}
?>
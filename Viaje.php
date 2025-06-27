<?php
require_once 'conexion.php';
require_once 'Empresa.php';
require_once 'ResponsableV.php';
require_once 'Pasajero.php';
require_once 'Participa.php';

class Viaje {
    private $id_viaje;
    private $v_destino;
    private $v_cantmaxpasajeros;
    private $v_importe;
    private $objEmpresa;
    private $objResponsableV;
    private $colPasajeros;
    private $activo;
    private $pdo;
    private $mensajeError;

    public function __construct() {
        $this->id_viaje = null;
        $this->v_destino = "";
        $this->v_cantmaxpasajeros = 0;
        $this->v_importe = 0.0;
        $this->objEmpresa = null;
        $this->objResponsableV = null;
        $this->colPasajeros = [];
        $this->activo = true;
        $this->pdo = conectarBD();
        $this->mensajeError = "";
    }

    public function cargar($v_destino, $v_cantmaxpasajeros, $v_importe, $objEmpresa, $objResponsableV, $activo = true) {
        $this->v_destino = $v_destino;
        $this->v_cantmaxpasajeros = $v_cantmaxpasajeros;
        $this->v_importe = $v_importe;
        $this->objEmpresa = $objEmpresa;
        $this->objResponsableV = $objResponsableV;
        $this->activo = $activo;
    }

    // Getters
    public function getIdViaje() { return $this->id_viaje; }
    public function getDestino() { return $this->v_destino; }
    public function getCantMaxPasajeros() { return $this->v_cantmaxpasajeros; }
    public function getImporte() { return $this->v_importe; }
    public function getEmpresa() { return $this->objEmpresa; }
    public function getResponsable() { return $this->objResponsableV; }
    public function getColPasajeros() { return $this->colPasajeros; }
    public function getActivo() { return $this->activo; }
    public function getMensajeError() { return $this->mensajeError; }

    // Setters
    public function setIdViaje($id_viaje) { $this->id_viaje = $id_viaje; }
    public function setDestino($v_destino) { $this->v_destino = $v_destino; }
    public function setCantMaxPasajeros($v_cantmaxpasajeros) { $this->v_cantmaxpasajeros = $v_cantmaxpasajeros; }
    public function setImporte($v_importe) { $this->v_importe = $v_importe; }
    public function setEmpresa($objEmpresa) { $this->objEmpresa = $objEmpresa; }
    public function setResponsable($objResponsableV) { $this->objResponsableV = $objResponsableV; }
    public function setColPasajeros($colPasajeros) { $this->colPasajeros = $colPasajeros; }
    public function setActivo($activo) { $this->activo = $activo; }
    public function setMensajeError($mensajeError) { $this->mensajeError = $mensajeError; }

    public function agregarPasajero(Pasajero $pasajero) {
        $resultado = false;
        $existe = false;
        $i = 0;
        $colPasajeros = $this->getColPasajeros();
        $cantPasajeros = count($colPasajeros);
        
        if ($cantPasajeros < $this->getCantMaxPasajeros()){
            
            while ($i < $cantPasajeros && !$existe) {
                if ($colPasajeros[$i]->getDocumento() === $pasajero->getDocumento()) {
                    $existe = true;
                }
                $i++;
            }
            
            if (!$existe) {
                $colPasajeros[] = $pasajero;
                $this->setColPasajeros($colPasajeros);
                $resultado = true;
            }
        }

        
        return $resultado;
    }

    public function quitarPasajero($documentoPasajero) {
        $resultado = false;
        $nuevaColeccion = [];
        $i = 0;
        $colPasajeros = $this->getColPasajeros();
        $cantPasajeros = count($colPasajeros);
        
        while ($i < $cantPasajeros) {
            if ($colPasajeros[$i]->getDocumento() !== $documentoPasajero) {
                $nuevaColeccion[] = $colPasajeros[$i];
            } else {
                $resultado = true;
            }
            $i++;
        }
        
        $colPasajeros = $nuevaColeccion;
        $this->setColPasajeros($colPasajeros);
        return $resultado;
    }

    private function cargarPasajeros($idViaje) {
        $participa = new Participa();
        return $participa->listarPasajerosPorViaje($idViaje);
    }

    public function insertar() {
        $resultado = false;
        
        try {
            $this->pdo->beginTransaction();
            
            $sql = "INSERT INTO viaje (v_destino, v_cantmaxpasajeros, v_importe, id_empresa, documento_responsable) 
                    VALUES (:destino, :cantmax, :importe, :idempresa, :docresponsable)";
            
            $stmt = $this->pdo->prepare($sql);
            $params = [
                ':destino' => $this->getDestino(),
                ':cantmax' => $this->getCantMaxPasajeros(),
                ':importe' => $this->getImporte(),
                ':idempresa' => $this->getEmpresa()->getIdEmpresa(),
                ':docresponsable' => $this->getResponsable()->getDocumento()
            ];
            
            if ($stmt->execute($params)) {
                $this->setIdViaje($this->pdo->lastInsertId());
                $todosInsertados = true;
                $i = 0;
                $colPasajeros = $this->getColPasajeros();
                $cantPasajeros = count($colPasajeros);
                
                while ($i < $cantPasajeros && $todosInsertados) {
                    $participa = new Participa();
                    $participa->setIdViaje($this->getIdViaje());
                    $participa->setDocumento($colPasajeros[$i]->getDocumento());
                    
                    if (!$participa->insertar()) {
                        $todosInsertados = false;
                        $this->setMensajeError($participa->getMensajeError());
                    }
                    $i++;
                }
                
                if ($todosInsertados) {
                    $this->pdo->commit();
                    $resultado = true;
                } else {
                    $this->pdo->rollBack();
                }
            }
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->mensajeError = $e->getMessage();
        }
        
        return $resultado;
    }

    public function modificar() {
        $resultado = false;
        
        try {
            $sql = "UPDATE viaje SET 
                    v_destino = :destino, 
                    v_cantmaxpasajeros = :cantmax, 
                    v_importe = :importe, 
                    id_empresa = :idempresa, 
                    documento_responsable = :docresponsable 
                    WHERE id_viaje = :id AND activo = TRUE";
            
            $stmt = $this->pdo->prepare($sql);
            $params = [
                ':destino' => $this->getDestino(),
                ':cantmax' => $this->getCantMaxPasajeros(),
                ':importe' => $this->getImporte(),
                ':idempresa' => $this->getEmpresa()->getIdEmpresa(),
                ':docresponsable' => $this->getResponsable()->getDocumento(),
                ':id' => $this->getIdViaje()
            ];
            
            $resultado = $stmt->execute($params);
            
        } catch (PDOException $e) {
            $this->setMensajeError("Error al modificar Viaje: " . $e->getMessage());
        }
        
        return $resultado;
    }

    public function eliminar() {
        $resultado = false;
        $this->setMensajeError("");

        try {
            // 2. Desactivar el viaje
            $sql = "UPDATE viaje SET activo = FALSE WHERE id_viaje = ?";
            $stmt = $this->pdo->prepare($sql);
            $resultado = $stmt->execute([$this->getIdViaje()]);

        } catch (PDOException $e) {
            $this->setMensajeError("Error al eliminar: " . $e->getMessage());
        }

        return $resultado;
    }


    public function buscar($id) {
        $resultado = false;
        
        try {
            $sql = "SELECT * FROM viaje WHERE id_viaje = ? AND activo = TRUE";
            $stmt = $this->pdo->prepare($sql);
            
            if ($stmt->execute([$id])) {
                $fila = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($fila) {
                    $empresa = new Empresa();
                    $responsable = new ResponsableV();
                    
                    if ($empresa->buscar($fila['id_empresa']) && $responsable->buscar($fila['documento_responsable'])) {
                        $this->cargar(
                            $fila['v_destino'],
                            $fila['v_cantmaxpasajeros'],
                            $fila['v_importe'],
                            $empresa,
                            $responsable,
                            $fila['activo']
                        );
                        
                        $this->setIdViaje($fila['id_viaje']);
                        $this->setColPasajeros($this->cargarPasajeros($id));
                        $resultado = true;
                    } else {
                        $this->setMensajeError("No se pudo cargar empresa o responsable");
                    }
                } else {
                    $this->setMensajeError("Viaje no encontrado");
                }
            }
            
        } catch (PDOException $e) {
            $this->setMensajeError("Error al buscar Viaje: " . $e->getMessage());
        }
        
        return $resultado;
    }

    public function listar($condicion = "") {
        $viajes = [];
        
        try {
            $sql = "SELECT * FROM viaje WHERE activo = TRUE";
            if ($condicion != "") {
                $sql .= " AND " . $condicion;
            }
            $sql .= " ORDER BY id_viaje";
            
            $stmt = $this->pdo->prepare($sql);
            
            if ($stmt->execute()) {
                while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $viaje = new Viaje();
                    if ($viaje->buscar($fila['id_viaje'])) {
                        $viajes[] = $viaje;
                    }
                }
            }
            
        } catch (PDOException $e) {
            $this->setMensajeError("Error al listar Viajes: " . $e->getMessage());
        }
        
        return $viajes;
    }


    public function reactivar() {
        $resultado = false;
        $this->setMensajeError("");

        try {
            $this->pdo->beginTransaction();

            // Reactivar el viaje
            $sql = "UPDATE viaje SET activo = TRUE WHERE id_viaje = ? AND activo = FALSE";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$this->getIdViaje()]);

            if ($stmt->rowCount() == 0) {
                $this->setMensajeError("No se puede reactivar: el viaje no existe o ya está activo");
                $this->pdo->rollBack();
            } else {
                // Reactivar las participaciones
                $sqlParticipa = "UPDATE participa SET activo = TRUE WHERE id_viaje = ?";
                $stmtPart = $this->pdo->prepare($sqlParticipa);
                $stmtPart->execute([$this->getIdViaje()]);

                // Reactivar pasajeros asociados al viaje
                $participa = new Participa();
                $pasajeros = $participa->listarTodosPasajerosPorViaje($this->getIdViaje());
                foreach ($pasajeros as $pasajero) {
                    $sqlPasajero = "UPDATE pasajero SET activo = TRUE WHERE documento = ?";
                    $stmtPas = $this->pdo->prepare($sqlPasajero);
                    $stmtPas->execute([$pasajero->getDocumento()]);
                }

                $this->setActivo(true);
                $this->pdo->commit();
                $resultado = true;
            }

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            $this->setMensajeError("Error al reactivar Viaje: " . $e->getMessage());
        }

        return $resultado;
    }



    


    public function __toString() {
        $empresaNombre = $this->getEmpresa() ? $this->getEmpresa()->getNombre() : "Sin empresa";
        $responsableNombre = $this->getResponsable() ? $this->getResponsable()->getNombre() . " " . $this->getResponsable()->getApellido() : "Sin responsable";

        return "Viaje ID: " . $this->getIdViaje() .
            " | Destino: " . $this->getDestino() .
            " | Capacidad: " . $this->getCantMaxPasajeros() .
            " | Importe: " . number_format($this->getImporte(), 2) .
            " | Empresa: " . $empresaNombre .
            " | Responsable: " . $responsableNombre;
    }


    public function ColPasajerosStr() {
        $str = "";
        
        if (empty($this->getColPasajeros())) {
            $str = "No hay pasajeros registrados";
        } else {
            $str = "Pasajeros:\n";
            $i = 0;
            $cantPasajeros = count($this->getColPasajeros());
            
            while ($i < $cantPasajeros) {
                $pasajero = $this->getColPasajeros()[$i];
                $str .= "- " . $pasajero->getNombre() . " " . $pasajero->getApellido() . " (Doc: " . $pasajero->getDocumento() . ")\n";
                $i++;
            }
        }
        
        return $str;
    }
}
?>
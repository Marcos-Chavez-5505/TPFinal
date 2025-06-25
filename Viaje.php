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
        $cantPasajeros = count($this->colPasajeros);
        
        while ($i < $cantPasajeros && !$existe) {
            if ($this->colPasajeros[$i]->getDocumento() === $pasajero->getDocumento()) {
                $existe = true;
            }
            $i++;
        }
        
        if (!$existe) {
            $this->colPasajeros[] = $pasajero;
            $resultado = true;
        }
        
        return $resultado;
    }

    public function quitarPasajero($documentoPasajero) {
        $resultado = false;
        $nuevaColeccion = [];
        $i = 0;
        $cantPasajeros = count($this->colPasajeros);
        
        while ($i < $cantPasajeros) {
            if ($this->colPasajeros[$i]->getDocumento() !== $documentoPasajero) {
                $nuevaColeccion[] = $this->colPasajeros[$i];
            } else {
                $resultado = true;
            }
            $i++;
        }
        
        $this->colPasajeros = $nuevaColeccion;
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
                ':destino' => $this->v_destino,
                ':cantmax' => $this->v_cantmaxpasajeros,
                ':importe' => $this->v_importe,
                ':idempresa' => $this->objEmpresa->getIdEmpresa(),
                ':docresponsable' => $this->objResponsableV->getDocumento()
            ];
            
            if ($stmt->execute($params)) {
                $this->id_viaje = $this->pdo->lastInsertId();
                $todosInsertados = true;
                $i = 0;
                $cantPasajeros = count($this->colPasajeros);
                
                while ($i < $cantPasajeros && $todosInsertados) {
                    $participa = new Participa();
                    $participa->setIdViaje($this->id_viaje);
                    $participa->setPDocumento($this->colPasajeros[$i]->getDocumento());
                    
                    if (!$participa->insertar()) {
                        $todosInsertados = false;
                        $this->mensajeError = $participa->getMensajeError();
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
                ':destino' => $this->v_destino,
                ':cantmax' => $this->v_cantmaxpasajeros,
                ':importe' => $this->v_importe,
                ':idempresa' => $this->objEmpresa->getIdEmpresa(),
                ':docresponsable' => $this->objResponsableV->getDocumento(),
                ':id' => $this->id_viaje
            ];
            
            $resultado = $stmt->execute($params);
            
        } catch (PDOException $e) {
            $this->mensajeError = "Error al modificar Viaje: " . $e->getMessage();
        }
        
        return $resultado;
    }

  public function eliminar() {
    $resultado = false;
    $this->setMensajeError("");

    try {
        // 1. Obtener todos los pasajeros del viaje
        $participa = new Participa();
        $pasajeros = $participa->listarPasajerosPorViaje($this->id_viaje);
        // array map ejecuta una funcion a un array, en este caso obtiene los documentos de los pasajeros
        $documentosPasajeros = array_map(fn($p) => $p->getDocumento(), $pasajeros);

        // 2. Desactivar todas las participaciones del viaje
        $this->pdo->exec("UPDATE participa SET activo = FALSE WHERE id_viaje = " . $this->id_viaje);

        // 3. Desactivar solo los pasajeros que no estén en otros viajes activos
        // array_unique evita que se repitan los documentos en los pasajeros si estan en otros viajes
        foreach (array_unique($documentosPasajeros) as $doc) {
            // Verificar si el pasajero está en otros viajes activos
            $sql = "SELECT COUNT(*) FROM participa p 
                         JOIN viaje v ON p.id_viaje = v.id_viaje 
                         WHERE p.documento = ? AND v.activo = TRUE AND p.id_viaje != ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$doc, $this->id_viaje]);
            $count = $stmt->fetchColumn();
            
            // Solo desactivar si no está en otros viajes activos
            if ($count == 0) {
                $this->pdo->exec("UPDATE pasajero SET activo = FALSE WHERE documento = '$doc'");
            }
        }

        // 4. Desactivar el viaje
        $sql = "UPDATE viaje SET activo = FALSE WHERE id_viaje = ?";
        $stmt = $this->pdo->prepare($sql);
        $resultado = $stmt->execute([$this->id_viaje]);

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
                        
                        $this->id_viaje = $fila['id_viaje'];
                        $this->colPasajeros = $this->cargarPasajeros($id);
                        $resultado = true;
                    } else {
                        $this->mensajeError = "No se pudo cargar empresa o responsable";
                    }
                } else {
                    $this->mensajeError = "Viaje no encontrado";
                }
            }
            
        } catch (PDOException $e) {
            $this->mensajeError = "Error al buscar Viaje: " . $e->getMessage();
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
            $this->mensajeError = "Error al listar Viajes: " . $e->getMessage();
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
            $stmt->execute([$this->id_viaje]);

            if ($stmt->rowCount() == 0) {
                $this->setMensajeError("No se puede reactivar: el viaje no existe o ya está activo");
                $this->pdo->rollBack();
                return false;
            }

            // Reactivar las participaciones
            $sqlParticipa = "UPDATE participa SET activo = TRUE WHERE id_viaje = ?";
            $stmtPart = $this->pdo->prepare($sqlParticipa);
            $stmtPart->execute([$this->id_viaje]);

            // Reactivar pasajeros asociados al viaje
            $participa = new Participa();
            $pasajeros = $participa->listarTodosPasajerosPorViaje($this->id_viaje); // sin filtro activo
            foreach ($pasajeros as $pasajero) {
                $sqlPasajero = "UPDATE pasajero SET activo = TRUE WHERE documento = ?";
                $stmtPas = $this->pdo->prepare($sqlPasajero);
                $stmtPas->execute([$pasajero->getDocumento()]);
            }

            $this->setActivo(true);
            $this->pdo->commit();
            $resultado = true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            $this->setMensajeError("Error al reactivar Viaje: " . $e->getMessage());
        }

        return $resultado;
    }


    


    public function __toString() {
        $empresaNombre = $this->objEmpresa ? $this->objEmpresa->getNombre() : "Sin empresa";
        $responsableNombre = $this->objResponsableV ? $this->objResponsableV->getNombre() . " " . $this->objResponsableV->getApellido() : "Sin responsable";

        return "Viaje ID: " . $this->id_viaje .
            " | Destino: " . $this->v_destino .
            " | Capacidad: " . $this->v_cantmaxpasajeros .
            " | Importe: " . number_format($this->v_importe, 2) .
            " | Empresa: " . $empresaNombre .
            " | Responsable: " . $responsableNombre;
    }


    public function ColPasajerosStr() {
        $str = "";
        
        if (empty($this->colPasajeros)) {
            $str = "No hay pasajeros registrados";
        } else {
            $str = "Pasajeros:\n";
            $i = 0;
            $cantPasajeros = count($this->colPasajeros);
            
            while ($i < $cantPasajeros) {
                $pasajero = $this->colPasajeros[$i];
                $str .= "- " . $pasajero->getNombre() . " " . $pasajero->getApellido() . " (Doc: " . $pasajero->getDocumento() . ")\n";
                $i++;
            }
        }
        
        return $str;
    }
}
?>
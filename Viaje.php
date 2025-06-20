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
    private $activo; // nuevo atributo para borrado lógico
    private $pdo;
    private $mensajeError;

    public function __construct(){
        $this->id_viaje = null;
        $this->v_destino = "";
        $this->v_cantmaxpasajeros = 0;
        $this->v_importe = 0.0;
        $this->objEmpresa = null;    
        $this->objResponsableV = null;
        $this->colPasajeros = [];
        $this->activo = true; // por defecto está activo
        $this->pdo = conectarBD();
        $this->mensajeError = "";
    }

    public function cargar($id_viaje, $v_destino, $v_cantmaxpasajeros, $v_importe, $objEmpresa, $objResponsableV, $colPasajeros = [], $activo = true){
        $this->setIdViaje($id_viaje);
        $this->setDestino($v_destino);
        $this->setCantMaxPasajeros($v_cantmaxpasajeros);
        $this->setImporte($v_importe);
        $this->setEmpresa($objEmpresa);
        $this->setResponsable($objResponsableV);
        $this->setColPasajeros($colPasajeros);
        $this->setActivo($activo);
    }

    // Getters
    public function getIdViaje(){ return $this->id_viaje; }
    public function getDestino(){ return $this->v_destino; }
    public function getCantMaxPasajeros(){ return $this->v_cantmaxpasajeros; }
    public function getImporte(){ return $this->v_importe; }
    public function getEmpresa(){ return $this->objEmpresa; }
    public function getResponsable(){ return $this->objResponsableV; }
    public function getColPasajeros(){ return $this->colPasajeros; }
    public function getActivo(){ return $this->activo; }
    public function getPdo(){ return $this->pdo; }
    public function getMensajeError(){ return $this->mensajeError; }

    // Setters
    public function setIdViaje($id_viaje){ $this->id_viaje = $id_viaje; }
    public function setDestino($v_destino){ $this->v_destino = $v_destino; }
    public function setCantMaxPasajeros($v_cantmaxpasajeros){ $this->v_cantmaxpasajeros = $v_cantmaxpasajeros; }
    public function setImporte($v_importe){ $this->v_importe = $v_importe; }
    public function setEmpresa($objEmpresa){ $this->objEmpresa = $objEmpresa; }
    public function setResponsable($objResponsableV){ $this->objResponsableV = $objResponsableV; }
    public function setColPasajeros($colPasajeros){ $this->colPasajeros = $colPasajeros; }
    public function setActivo($activo){ $this->activo = $activo; }
    public function setMensajeError($mensajeError){ $this->mensajeError = $mensajeError; }

    public function agregarPasajero(Pasajero $pasajero) {
    $existe = false;

    foreach ($this->colPasajeros as $p) {
        $existe = $existe || ($p->getDocumento() === $pasajero->getDocumento());
    }

    if (!$existe) {
        $this->colPasajeros[] = $pasajero;

        $participa = new Participa();
        $participa->setIdViaje($this->getIdViaje());
        $participa->setPDocumento($pasajero->getDocumento());
        $participa->insertar();
    }
}


    // Quitar pasajero de colección y desactivar relación en participa
    public function quitarPasajero($documentoPasajero) {
        foreach ($this->colPasajeros as $key => $p) {
            if ($p->getDocumento() === $documentoPasajero) {
                unset($this->colPasajeros[$key]);
                // Reindexar
                $this->colPasajeros = array_values($this->colPasajeros);

                // Borrado lógico en participa
                $participa = new Participa();
                $participa->setIdViaje($this->getIdViaje());
                $participa->setPDocumento($documentoPasajero);
                $participa->eliminar();
                return true;
            }
        }
        return false;
    }

    // Cargar pasajeros desde BD via tabla participa
    private function cargarPasajeros($idViaje) {
        $participa = new Participa();
        return $participa->listarPasajerosPorViaje($idViaje);
    }

    public function insertar() {
        $resultado = false;
        $sql = "INSERT INTO viaje (v_destino, v_cantmaxpasajeros, v_importe, id_empresa, r_numeroempleado, activo) VALUES (?, ?, ?, ?, ?, ?)";
        try {
            $stmt = $this->getPdo()->prepare($sql);
            if ($stmt->execute([
                $this->getDestino(),
                $this->getCantMaxPasajeros(),
                $this->getImporte(),
                $this->getEmpresa()->getIdEmpresa(),
                $this->getResponsable()->getIdResponsable(),
                $this->getActivo()
            ])) {
                $this->setIdViaje($this->getPdo()->lastInsertId());

                // Insertar relaciones pasajero-viaje
                foreach ($this->colPasajeros as $pasajero) {
                    $participa = new Participa();
                    $participa->setIdViaje($this->getIdViaje());
                    $participa->setPDocumento($pasajero->getDocumento());
                    $participa->insertar();
                }
                $resultado = true;
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al insertar Viaje: " . $e->getMessage());
        }
        return $resultado;
    }

    public function modificar() {
        $resultado = false;
        $sql = "UPDATE viaje SET v_destino = ?, v_cantmaxpasajeros = ?, v_importe = ?, id_empresa = ?, r_numeroempleado = ? WHERE id_viaje = ? AND activo = TRUE";
        try {
            $stmt = $this->getPdo()->prepare($sql);
            $resultado = $stmt->execute([
                $this->getDestino(),
                $this->getCantMaxPasajeros(),
                $this->getImporte(),
                $this->getEmpresa()->getIdEmpresa(),
                $this->getResponsable()->getIdResponsable(),
                $this->getIdViaje()
            ]);
            if ($resultado) {
                // Opcional: actualizar pasajeros
                // Aquí deberías manejar la actualización de relaciones participa,
                // si fuera necesario (agregar/quitar pasajeros)
                // Por simplicidad no lo implemento aquí, pero puede hacerse.
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al modificar Viaje: " . $e->getMessage());
        }
        return $resultado;
    }

    public function eliminar() {
        $resultado = false;
        $this->setMensajeError("");
        $sqlViaje = "UPDATE viaje SET activo = FALSE WHERE id_viaje = ? AND activo = TRUE";
        try {
            $stmtViaje = $this->getPdo()->prepare($sqlViaje);
            if ($stmtViaje->execute([$this->getIdViaje()])) {
                // Borrado lógico en participa: desactivar todas las participaciones de ese viaje
                $sqlParticipa = "UPDATE participa SET activo = FALSE WHERE id_viaje = ?";
                $stmtParticipa = $this->getPdo()->prepare($sqlParticipa);
                $stmtParticipa->execute([$this->getIdViaje()]);
                $this->setActivo(false);
                $resultado = true;
            } else {
                $this->setMensajeError("El viaje no existe o ya está eliminado.");
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al eliminar Viaje: " . $e->getMessage());
        }
        return $resultado;
    }

    public function reactivar() {
        $resultado = false;
        $this->setMensajeError("");
        $sql = "UPDATE viaje SET activo = TRUE WHERE id_viaje = ? AND activo = FALSE";
        try {
            $stmt = $this->getPdo()->prepare($sql);
            $resultado = $stmt->execute([$this->getIdViaje()]);
            if ($stmt->rowCount() > 0) {
                $this->setActivo(true);
                // Reactivar también las participaciones
                $sqlParticipa = "UPDATE participa SET activo = TRUE WHERE id_viaje = ?";
                $stmtParticipa = $this->getPdo()->prepare($sqlParticipa);
                $stmtParticipa->execute([$this->getIdViaje()]);
            } else {
                $this->setMensajeError("El viaje no existe o ya está activo.");
                $resultado = false;
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al reactivar Viaje: " . $e->getMessage());
        }
        return $resultado;
    }

    public function buscar($id) {
        $resultado = false;
        $sql = "SELECT * FROM viaje WHERE id_viaje = ? AND activo = TRUE";
        try {
            $stmt = $this->getPdo()->prepare($sql);
            if ($stmt->execute([$id])) {
                if ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $empresa = new Empresa();
                    $empresa->buscar($fila['id_empresa']);
                    $responsable = new ResponsableV();
                    $responsable->buscar($fila['r_numeroempleado']);
                    
                    $this->cargar(
                        $fila['id_viaje'],
                        $fila['v_destino'],
                        $fila['v_cantmaxpasajeros'],
                        $fila['v_importe'],
                        $empresa,
                        $responsable,
                        [], // colección pasajeros vacía inicialmente
                        $fila['activo']
                    );

                    // Cargar pasajeros activos relacionados
                    $this->colPasajeros = $this->cargarPasajeros($id);
                    $resultado = true;
                }
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al buscar Viaje: " . $e->getMessage());
        }
        return $resultado;
    }

    public function listar($condicion = "") {
        $arregloViaje = null;
        $sql = "SELECT * FROM viaje WHERE activo = TRUE";
        if ($condicion != "") {
            $sql .= " AND " . $condicion;
        }
        $sql .= " ORDER BY id_viaje";

        try {
            $stmt = $this->getPdo()->prepare($sql);
            if ($stmt->execute()) {
                $arregloViaje = [];
                while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $viaje = new Viaje();
                    $empresa = new Empresa();
                    $empresa->buscar($fila['id_empresa']);
                    $responsable = new ResponsableV();
                    $responsable->buscar($fila['r_numeroempleado']);
                    $viaje->cargar(
                        $fila['id_viaje'],
                        $fila['v_destino'],
                        $fila['v_cantmaxpasajeros'],
                        $fila['v_importe'],
                        $empresa,
                        $responsable,
                        [], // pasajeros vacíos
                        $fila['activo']
                    );

                    // Cargar pasajeros para cada viaje
                    $viaje->setColPasajeros($this->cargarPasajeros($fila['id_viaje']));

                    $arregloViaje[] = $viaje;
                }
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error en listar Viaje: " . $e->getMessage());
        }
        return $arregloViaje;
    }

    public function __toString() {
        $estado = $this->getActivo() ? "ACTIVO" : "ELIMINADO";
        return "Viaje [ID: " . $this->getIdViaje() .
            ", Destino: " . $this->getDestino() .
            ", Cant Max: " . $this->getCantMaxPasajeros() .
            ", Importe: " . $this->getImporte() .
            ", Empresa: " . $this->getEmpresa()->getIdEmpresa() .
            ", Empleado: " . $this->getResponsable()->getIdResponsable() .
            ", Estado: " . $estado . "]";
    }

    public function ColPasajerosStr(){
        $unaColeccion = $this->getColPasajeros();
        $coleccionStr = "No hay elementos";
        if ($unaColeccion != null && count($unaColeccion) > 0){
            $coleccionStr = "";
            foreach ($unaColeccion as $elemento){
                $coleccionStr .= "\n" . $elemento;
            }
            $coleccionStr .= "\n";
        }
        return $coleccionStr;
    }
}
?>

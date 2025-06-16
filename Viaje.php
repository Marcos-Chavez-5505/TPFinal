<?php
require_once 'conexion.php';
require_once 'Empresa.php';
require_once 'ResponsableV.php';

class Viaje {
    private $id_viaje;
    private $v_destino;
    private $v_cantmaxpasajeros; // quizas debamos modificar el nombre que tiene la columna en la base de datos
    private $v_importe;
    private $objEmpresa;    
    private $objResponsableV;
    private $colPasajeros;
    private $pdo;
    private $mensajeError; // nuevo atributo para guardar el mensaje de error

    public function __construct(){
        $this->id_viaje = null;
        $this->v_destino = "";
        $this->v_cantmaxpasajeros = 0;
        $this->v_importe = 0.0;
        $this->objEmpresa = null;    
        $this->objResponsableV = null;
        $this->colPasajeros = [];
        $this->pdo = conectarBD();
        $this->mensajeError = "";  // inicializamos vacío
    }

    public function cargar($id_viaje, $v_destino, $v_cantmaxpasajeros, $v_importe, $objEmpresa, $objResponsableV, $colPasajeros = []){
        $this->setIdViaje($id_viaje);
        $this->setDestino($v_destino);
        $this->setCantMaxPasajeros($v_cantmaxpasajeros);
        $this->setImporte($v_importe);
        $this->setEmpresa($objEmpresa);
        $this->setResponsable($objResponsableV);
        $this->setColPasajeros($colPasajeros);
    }

    // Getter PDO
    public function getPdo() {return $this->pdo;}

    // Getter y Setter mensajeError
    public function getMensajeError() { return $this->mensajeError; }
    public function setMensajeError($mensajeError) { $this->mensajeError = $mensajeError; }

    // Getters
    public function getIdViaje(){return $this->id_viaje;}
    public function getDestino(){return $this->v_destino;}
    public function getCantMaxPasajeros(){return $this->v_cantmaxpasajeros;}
    public function getImporte(){return $this->v_importe;}
    public function getEmpresa(){return $this->objEmpresa;}
    public function getResponsable(){return $this->objResponsableV;}
    public function getColPasajeros(){return $this->colPasajeros;}

    // Setters
    public function setIdViaje($id_viaje){$this->id_viaje = $id_viaje;}
    public function setDestino($v_destino){$this->v_destino = $v_destino;}
    public function setCantMaxPasajeros($v_cantmaxpasajeros){$this->v_cantmaxpasajeros = $v_cantmaxpasajeros;}
    public function setImporte($v_importe){$this->v_importe = $v_importe;}
    public function setEmpresa($objEmpresa){$this->objEmpresa = $objEmpresa;}
    public function setResponsable($objResponsableV){$this->objResponsableV = $objResponsableV;}
    public function setColPasajeros($colPasajeros){$this->colPasajeros = $colPasajeros;}

    /** Al ser llamada realiza la operación de inserción sobre la BD con los atributos predefinidos en la clase
     * Retorna true si tuvo éxito, false si no lo tuvo
     * @return bool
     */
    public function insertar() {
        $resultado = false;
        $sql = "INSERT INTO viaje (v_destino, v_cantmaxpasajeros, v_importe, id_empresa, r_numeroempleado) VALUES (?, ?, ?, ?, ?)";
        try {
            $stmt = $this->getPdo()->prepare($sql);
            if ($stmt->execute([
                $this->getDestino(),
                $this->getCantMaxPasajeros(),
                $this->getImporte(),
                $this->getEmpresa()->getIdEmpresa(),
                $this->getResponsable()->getIdResponsable()
            ])) {
                $this->setIdViaje($this->getPdo()->lastInsertId());
                $resultado = true;
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al insertar Viaje: " . $e->getMessage());
        }
        return $resultado;
    }

    /** Al ser llamada realiza la operación de actualización sobre la BD con los atributos predefinidos en la clase
     * Retorna true si tuvo éxito, false si no lo tuvo
     * @return bool
     */
    public function modificar() {
        $resultado = false;
        $sql = "UPDATE viaje SET v_destino = ?, v_cantmaxpasajeros = ?, v_importe = ?, id_empresa = ?, r_numeroempleado = ? WHERE id_viaje = ?";
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
        } catch (PDOException $e) {
            $this->setMensajeError("Error al modificar Viaje: " . $e->getMessage());
        }
        return $resultado;
    }

    /** Al ser llamada realiza la operación de eliminación sobre la BD a la tupla identificada con los atributos predefinidos en la clase
     * Retorna true si tuvo éxito, false si no lo tuvo
     * @return bool
     */
    public function eliminar() {
        $resultado = false;
        $sql = "DELETE FROM viaje WHERE id_viaje = ?";
        try {
            $stmt = $this->getPdo()->prepare($sql);
            $resultado = $stmt->execute([$this->getIdViaje()]);
        } catch (PDOException $e) {
            $this->setMensajeError("Error al eliminar Viaje: " . $e->getMessage());
        }
        return $resultado;
    }

    /** 
     * Busca un viaje por ID en la base de datos y carga el objeto con los datos encontrados.
     * Retorna true si encontró y cargó el viaje, false si no.
     * @param int $id
     * @return bool
     */
    public function buscar($id) {
        $resultado = false;
        $sql = "SELECT * FROM viaje WHERE id_viaje = ?";
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
                        $responsable
                    );
                    $resultado = true;
                }
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al buscar Viaje: " . $e->getMessage());
        }
        return $resultado;
    }

    /** Lista viajes, opcionalmente con condición.
     * 
     * @param mixed $condicion
     * @return array|null
     */
    public function listar($condicion = "") {
        $arregloViaje = null;
        $sql = "SELECT * FROM viaje";
        if ($condicion != "") {
            $sql .= " WHERE " . $condicion;
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
                        $responsable
                    );
                    array_push($arregloViaje, $viaje);
                }
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error en listar Viaje: " . $e->getMessage());
        }

        return $arregloViaje;
    }

    public function __toString() {
        return "Viaje [ID: " . $this->getIdViaje() .
            ", Destino: " . $this->getDestino() .
            ", Cant Max: " . $this->getCantMaxPasajeros() .
            ", Importe: " . $this->getImporte() .
            ", Empresa: " . $this->getEmpresa()->getIdEmpresa() .
            ", Empleado: " . $this->getResponsable()->getNumEmpleado() . "]";
    }
}
?>

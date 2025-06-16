<?php
require_once 'conexion.php';
require_once 'Viaje.php';

class Pasajero {
    private $p_documento;
    private $p_nombre;
    private $p_apellido;
    private $p_telefono;                                
    private $objViaje; // Referencia a objeto Viaje
    private $pdo;

    public function __construct(){
        $this->p_documento = "";
        $this->p_nombre = "";
        $this->p_apellido = "";
        $this->p_telefono = "";
        $this->objViaje = null;
        $this->pdo = conectarBD();
    }

    // Getters
    public function getP_documento(){ return $this->p_documento; }
    public function getP_nombre(){ return $this->p_nombre; }
    public function getP_apellido(){ return $this->p_apellido; }
    public function getP_telefono(){ return $this->p_telefono; }
    public function getViaje(){ return $this->objViaje; }

    // Setters
    public function setP_documento($p_documento){ $this->p_documento = $p_documento; }
    public function setP_nombre($p_nombre){ $this->p_nombre = $p_nombre; }
    public function setP_apellido($p_apellido){ $this->p_apellido = $p_apellido; }
    public function setP_telefono($p_telefono){ $this->p_telefono = $p_telefono; }
    public function setViaje($objViaje){ $this->objViaje = $objViaje; }

    /** Carga todos los atributos */
    public function cargar($p_documento, $p_nombre, $p_apellido, $p_telefono, $objViaje){
        $this->setP_documento($p_documento);
        $this->setP_nombre($p_nombre);
        $this->setP_apellido($p_apellido);
        $this->setP_telefono($p_telefono);
        $this->setViaje($objViaje);
    }

    /** Inserta el pasajero en la BD */
    public function insertar(){
        $sql = "INSERT INTO pasajero (p_documento, p_nombre, p_apellido, p_telefono, id_viaje) VALUES (?, ?, ?, ?, ?)";
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                $this->getP_documento(),
                $this->getP_nombre(),
                $this->getP_apellido(),
                $this->getP_telefono(),
                $this->getViaje()->getIdViaje()
            ]);
        } catch (PDOException $e) {
            error_log("Error al insertar Pasajero: " . $e->getMessage());
            return false;
        }
    }

    /** Modifica el pasajero en la BD */
    public function modificar(){
        $sql = "UPDATE pasajero SET p_nombre = ?, p_apellido = ?, p_telefono = ?, id_viaje = ? WHERE p_documento = ?";
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                $this->getP_nombre(),
                $this->getP_apellido(),
                $this->getP_telefono(),
                $this->getViaje()->getIdViaje(),
                $this->getP_documento()
            ]);
        } catch (PDOException $e) {
            error_log("Error al modificar Pasajero: " . $e->getMessage());
            return false;
        }
    }

    /** Elimina el pasajero en la BD */
    public function eliminar(){
        $sql = "DELETE FROM pasajero WHERE p_documento = ?";
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$this->getP_documento()]);
        } catch (PDOException $e) {
            error_log("Error al eliminar Pasajero: " . $e->getMessage());
            return false;
        }
    }

    /** Busca un pasajero por documento y carga el objeto */
    public function buscar($p_documento){
        $encontro = false;
        $sql = "SELECT * FROM pasajero WHERE p_documento = ?";
        try {
            $stmt = $this->pdo->prepare($sql);
            if ($stmt->execute([$p_documento])) {
                if ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $objViaje = new Viaje();
                    $objViaje->buscar($fila['id_viaje']);
                    $this->cargar(
                        $fila['p_documento'],
                        $fila['p_nombre'],
                        $fila['p_apellido'],
                        $fila['p_telefono'],
                        $objViaje
                    );
                    $encontro = true;
                }
            }
        } catch (PDOException $e) {
            error_log("Error al buscar Pasajero: " . $e->getMessage());
        }
        return $encontro;
    }

    /** Lista todos los pasajeros, opcionalmente con condición */
    public function listar($condicion = ""){
        $arregloPasajeros = null;
        $sql = "SELECT * FROM pasajero";
        if ($condicion != "") {
            $sql .= " WHERE " . $condicion;
        }
        $sql .= " ORDER BY p_apellido, p_nombre";

        try {
            $stmt = $this->pdo->prepare($sql);
            if ($stmt->execute()) {
                $arregloPasajeros = [];
                while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $objViaje = new Viaje();
                    $objViaje->buscar($fila['id_viaje']);
                    $pasajero = new Pasajero();
                    $pasajero->cargar(
                        $fila['p_documento'],
                        $fila['p_nombre'],
                        $fila['p_apellido'],
                        $fila['p_telefono'],
                        $objViaje
                    );
                    $arregloPasajeros[] = $pasajero;
                }
            }
        } catch (PDOException $e) {
            error_log("Error en listar Pasajeros: " . $e->getMessage());
        }
        return $arregloPasajeros;
    }

    public function __toString(){
        return "Pasajero [Documento: " . $this->getP_documento() .
               ", Nombre: " . $this->getP_nombre() .
               ", Apellido: " . $this->getP_apellido() .
               ", Teléfono: " . $this->getP_telefono() .
               ", Viaje: (" . $this->getViaje() . ")]";
    }
}
?>

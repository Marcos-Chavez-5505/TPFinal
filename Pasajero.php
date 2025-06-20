<?php
require_once 'conexion.php';
require_once 'Viaje.php';
require_once 'Persona.php';

class Pasajero extends Persona {
    private $p_documento;
    private $p_telefono;
    private $pdo;
    private $mensajeError;

    public function __construct() {
        parent::__construct();
        $this->p_documento = "";
        $this->p_telefono = "";
        $this->pdo = conectarBD();
        $this->mensajeError = "";
    }

    // Getters
    public function getDocumento() { return $this->p_documento; }
    public function getTelefono() { return $this->p_telefono; }
    public function getMensajeError() { return $this->mensajeError; }
    public function getPdo() { return $this->pdo; }

    // Setters
    public function setDocumento($doc) { $this->p_documento = $doc; }
    public function setTelefono($tel) { $this->p_telefono = $tel; }
    public function setMensajeError($msg) { $this->mensajeError = $msg; }

    public function cargar($documento, $nombre, $apellido, $telefono) {
        $this->setDocumento($documento);
        $this->setNombre($nombre);
        $this->setApellido($apellido);
        $this->setTelefono($telefono);
        $this->setActivo(true);
    }

    public function insertar() {
        $sql = "INSERT INTO pasajero (p_documento, p_nombre, p_apellido, p_telefono, activo) VALUES (?, ?, ?, ?, ?)";
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                $this->getDocumento(),
                $this->getNombre(),
                $this->getApellido(),
                $this->getTelefono(),
                $this->getActivo()
            ]);
        } catch (PDOException $e) {
            $this->setMensajeError("Error al insertar Pasajero: " . $e->getMessage());
            return false;
        }
    }

    public function modificar() {
        $sql = "UPDATE pasajero SET p_nombre = ?, p_apellido = ?, p_telefono = ? WHERE p_documento = ? AND activo = TRUE";
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                $this->getNombre(),
                $this->getApellido(),
                $this->getTelefono(),
                $this->getDocumento()
            ]);
        } catch (PDOException $e) {
            $this->setMensajeError("Error al modificar Pasajero: " . $e->getMessage());
            return false;
        }
    }

    public function eliminar() {
        $sql = "UPDATE pasajero SET activo = FALSE WHERE p_documento = ? AND activo = TRUE";
        try {
            $stmt = $this->pdo->prepare($sql);
            if ($stmt->execute([$this->getDocumento()]) && $stmt->rowCount() > 0) {
                $this->setActivo(false);
                return true;
            }
            $this->setMensajeError("El pasajero no existe o ya está eliminado.");
        } catch (PDOException $e) {
            $this->setMensajeError("Error al eliminar Pasajero: " . $e->getMessage());
        }
        return false;
    }

    public function buscar($documento) {
        $sql = "SELECT * FROM pasajero WHERE p_documento = ? AND activo = TRUE";
        try {
            $stmt = $this->pdo->prepare($sql);
            if ($stmt->execute([$documento]) && $fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->cargar($fila['p_documento'], $fila['p_nombre'], $fila['p_apellido'], $fila['p_telefono']);
                $this->setActivo($fila['activo']);
                return true;
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al buscar Pasajero: " . $e->getMessage());
        }
        return false;
    }

    public function listar($condicion = "") {
        $sql = "SELECT * FROM pasajero WHERE activo = TRUE";
        if ($condicion !== "") {
            $sql .= " AND $condicion";
        }
        $sql .= " ORDER BY p_documento";

        $coleccion = [];
        try {
            $stmt = $this->pdo->query($sql);
            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $obj = new Pasajero();
                $obj->cargar($fila['p_documento'], $fila['p_nombre'], $fila['p_apellido'], $fila['p_telefono']);
                $obj->setActivo($fila['activo']);
                $coleccion[] = $obj;
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al listar Pasajeros: " . $e->getMessage());
        }

        return $coleccion;
    }

    public function agregarViaje($idViaje) {
        $sql = "INSERT INTO participa (p_documento, id_viaje, activo) VALUES (?, ?, TRUE)";
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$this->getDocumento(), $idViaje]);
        } catch (PDOException $e) {
            $this->setMensajeError("Error al agregar viaje al pasajero: " . $e->getMessage());
            return false;
        }
    }

    public function quitarViaje($idViaje) {
        $sql = "UPDATE participa SET activo = FALSE WHERE p_documento = ? AND id_viaje = ? AND activo = TRUE";
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$this->getDocumento(), $idViaje]);
        } catch (PDOException $e) {
            $this->setMensajeError("Error al quitar viaje del pasajero: " . $e->getMessage());
            return false;
        }
    }

    public function listarViajes() {
        $sql = "SELECT v.* FROM viaje v 
                INNER JOIN participa p ON v.id_viaje = p.id_viaje 
                WHERE p.p_documento = ? AND p.activo = TRUE AND v.activo = TRUE";
        $coleccion = [];
        try {
            $stmt = $this->pdo->prepare($sql);
            if ($stmt->execute([$this->getDocumento()])) {
                while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $viaje = new Viaje();
                    $viaje->buscar($fila['id_viaje']); // carga completa
                    $coleccion[] = $viaje;
                }
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al listar viajes del pasajero: " . $e->getMessage());
        }
        return $coleccion;
    }

    public function __toString() {
        $estado = $this->getActivo() ? "ACTIVO" : "ELIMINADO";
        $viajes = $this->listarViajes();
        $listaViajes = count($viajes) > 0
            ? implode(", ", array_map(fn($v) => $v->getDestino(), $viajes))
            : "No tiene viajes asignados";
        return "Documento: " . $this->getDocumento() .
               ", Nombre: " . $this->getNombre() .
               ", Apellido: " . $this->getApellido() .
               ", Teléfono: " . $this->getTelefono() .
               ", Estado: " . $estado .
               ", Viajes: " . $listaViajes . "<br>";
    }
}
?>

<?php
require_once 'conexion.php';
require_once 'Viaje.php';
require_once 'Pasajero.php';

class Participa {
    private $id_viaje;
    private $p_documento;
    private $activo;
    private $pdo;
    private $mensajeError;

    public function __construct() {
        $this->id_viaje = null;
        $this->p_documento = "";
        $this->activo = true;
        $this->pdo = conectarBD();
        $this->mensajeError = "";
    }

    // Getters
    public function getIdViaje() { return $this->id_viaje; }
    public function getPDocumento() { return $this->p_documento; }
    public function getActivo() { return $this->activo; }
    public function getMensajeError() { return $this->mensajeError; }

    // Setters
    public function setIdViaje($id) { $this->id_viaje = $id; }
    public function setPDocumento($doc) { $this->p_documento = $doc; }
    public function setActivo($activo) { $this->activo = $activo; }
    public function setMensajeError($msg) { $this->mensajeError = $msg; }

    // Insertar relación pasajero-viaje (participación activa)
    public function insertar() {
        // Verifica si ya existe (activo o inactivo)
        if ($this->buscar($this->getIdViaje(), $this->getPDocumento(), false)) {
            return $this->reactivar();
        }

        $sql = "INSERT INTO participa (id_viaje, p_documento, activo) VALUES (?, ?, ?)";
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$this->getIdViaje(), $this->getPDocumento(), true]);
        } catch (PDOException $e) {
            $this->setMensajeError("Error al insertar Participa: " . $e->getMessage());
            return false;
        }
    }

    // Borrado lógico: desactivar participación
    public function eliminar() {
        $sql = "UPDATE participa SET activo = FALSE WHERE id_viaje = ? AND p_documento = ? AND activo = TRUE";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$this->getIdViaje(), $this->getPDocumento()]);
            $actualizadas = $stmt->rowCount();
            if ($actualizadas > 0) {
                $this->setActivo(false);
            } else {
                $this->setMensajeError("No existe la participación activa para eliminar.");
            }
            return $actualizadas > 0;
        } catch (PDOException $e) {
            $this->setMensajeError("Error al eliminar Participa: " . $e->getMessage());
            return false;
        }
    }

    // Reactivar una participación eliminada (opcional)
    public function reactivar() {
        $sql = "UPDATE participa SET activo = TRUE WHERE id_viaje = ? AND p_documento = ? AND activo = FALSE";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$this->getIdViaje(), $this->getPDocumento()]);
            $reactivadas = $stmt->rowCount();
            if ($reactivadas > 0) {
                $this->setActivo(true);
            } else {
                $this->setMensajeError("No existe la participación inactiva para reactivar.");
            }
            return $reactivadas > 0;
        } catch (PDOException $e) {
            $this->setMensajeError("Error al reactivar Participa: " . $e->getMessage());
            return false;
        }
    }

    // Buscar participación específica, activa o no según parámetro
    public function buscar($id_viaje, $p_documento, $soloActivos = true) {
        $sql = "SELECT * FROM participa WHERE id_viaje = ? AND p_documento = ?";
        if ($soloActivos) {
            $sql .= " AND activo = TRUE";
        }

        try {
            $stmt = $this->pdo->prepare($sql);
            if ($stmt->execute([$id_viaje, $p_documento])) {
                if ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $this->setIdViaje($fila['id_viaje']);
                    $this->setPDocumento($fila['p_documento']);
                    $this->setActivo($fila['activo']);
                    return true;
                }
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al buscar Participa: " . $e->getMessage());
        }
        return false;
    }

    // Listar pasajeros activos de un viaje
    public function listarPasajerosPorViaje($id_viaje) {
        $lista = [];
        $sql = "SELECT p.* FROM pasajero p
                INNER JOIN participa pa ON p.p_documento = pa.p_documento
                WHERE pa.id_viaje = ? AND pa.activo = TRUE AND p.activo = TRUE";
        try {
            $stmt = $this->pdo->prepare($sql);
            if ($stmt->execute([$id_viaje])) {
                while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $pasajero = new Pasajero();
                    $pasajero->cargar(
                        $fila['p_documento'],
                        $fila['p_nombre'],
                        $fila['p_apellido'],
                        $fila['p_telefono']
                    );
                    $pasajero->setActivo($fila['activo']);
                    $lista[] = $pasajero;
                }
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al listar pasajeros por viaje: " . $e->getMessage());
        }
        return $lista;
    }

    // Listar viajes activos de un pasajero
    public function listarViajesPorPasajero($p_documento) {
        $lista = [];
        $sql = "SELECT v.* FROM viaje v
                INNER JOIN participa pa ON v.id_viaje = pa.id_viaje
                WHERE pa.p_documento = ? AND pa.activo = TRUE AND v.activo = TRUE";
        try {
            $stmt = $this->pdo->prepare($sql);
            if ($stmt->execute([$p_documento])) {
                while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $empresa = new Empresa();
                    $empresa->buscar($fila['id_empresa']);
                    $responsable = new ResponsableV();
                    $responsable->buscar($fila['r_numeroempleado']);
                    $viaje = new Viaje();
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
                    $lista[] = $viaje;
                }
            }
        } catch (PDOException $e) {
            $this->setMensajeError("Error al listar viajes por pasajero: " . $e->getMessage());
        }
        return $lista;
    }
}
?>

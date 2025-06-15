<?php
require_once 'conexion.php';

class Viaje {
    private $id_viaje;
    private $destino;
    private $cant_max_pasajeros;
    private $importe;
    private $id_empresa;
    private $r_numero_empleado;
    private $pdo;

    public function __construct($id_viaje, $destino, $cant_max_pasajeros, $importe, $id_empresa, $r_numero_empleado){
        $this->id_viaje = $id_viaje;
        $this->destino = $destino;
        $this->cant_max_pasajeros = $cant_max_pasajeros;
        $this->importe = $importe;
        $this->id_empresa = $id_empresa;    
        $this->r_numero_empleado = $r_numero_empleado;
        $this->pdo = conectarBD();
    }


    // Getters
    public function getIdViaje(){
        return $this->id_viaje;
    }

    public function getDestino(){
        return $this->destino;
    }

    public function getCantMaxPasajeros(){
        return $this->cant_max_pasajeros;
    }

    public function getImporte(){
        return $this->importe;
    }

    public function getIdEmpresa(){
        return $this->id_empresa;
    }

    public function getRNumeroEmpleado(){
        return $this->r_numero_empleado;
    }

    // Setters
    public function setIdViaje($id_viaje){
        $this->id_viaje = $id_viaje;
    }

    public function setDestino($destino){
        $this->destino = $destino;
    }

    public function setCantMaxPasajeros($cant_max_pasajeros){
        $this->cant_max_pasajeros = $cant_max_pasajeros;
    }

    public function setImporte($importe){
        $this->importe = $importe;
    }

    public function setIdEmpresa($id_empresa){
        $this->id_empresa = $id_empresa;
    }

    public function setRNumeroEmpleado($r_numero_empleado){
        $this->r_numero_empleado = $r_numero_empleado;
    }

    public function insertar(){
        $sql = "INSERT INTO viaje (destino, cant_max_pasajeros, importe, id_empresa, r_numero_empleado) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $resultado = $stmt->execute([
            $this->getDestino(),
            $this->getCantMaxPasajeros(),
            $this->getImporte(),
            $this->getIdEmpresa(),
            $this->getRNumeroEmpleado()
        ]);
        if($resultado){
            $this->setIdViaje($this->pdo->lastInsertId());
        }
        return $resultado;
    }

    public function modificar(){
        $sql = "UPDATE viaje SET destino = ?, cant_max_pasajeros = ?, importe = ?, id_empresa = ?, r_numero_empleado = ? WHERE id_viaje = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $this->getDestino(),
            $this->getCantMaxPasajeros(),
            $this->getImporte(),
            $this->getIdEmpresa(),
            $this->getRNumeroEmpleado(),
            $this->getIdViaje()
        ]);
    }

    public function eliminar(){
        $sql = "DELETE FROM viaje WHERE id_viaje = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$this->getIdViaje()]);
    }

    public static function buscar($id){
        $pdo = conectarBD();
        $sql = "SELECT * FROM viaje WHERE id_viaje = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $fila = $stmt->fetch();
        if($fila){
            return new Viaje($fila['id_viaje'], $fila['destino'], $fila['cant_max_pasajeros'], $fila['importe'], $fila['id_empresa'], $fila['r_numero_empleado']);
        }
        return null;
    }

    public static function listar(){
        $pdo = conectarBD();
        $sql = "SELECT * FROM viaje";
        $stmt = $pdo->query($sql);
        $viajes = [];
        while($fila = $stmt->fetch()){
            $viajes[] = new Viaje($fila['id_viaje'], $fila['destino'], $fila['cant_max_pasajeros'], $fila['importe'], $fila['id_empresa'], $fila['r_numero_empleado']);
        }
        return $viajes;
    }

    public function __toString(){
        return "Viaje [ID: ".$this->getIdViaje().", Destino: ".$this->getDestino().", Cant Max: ".$this->getCantMaxPasajeros().", Importe: ".$this->getImporte().", Empresa: ".$this->getIdEmpresa().", Empleado: ".$this->getRNumeroEmpleado()."]";
    }
}
?>

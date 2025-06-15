<?php
require_once 'conexion.php';

class Empresa {
    private $id_empresa;
    private $e_nombre;
    private $e_direccion;
    private $pdo;

    public function __construct($id_empresa, $e_nombre, $e_direccion){
        $this->id_empresa = $id_empresa;
        $this->e_nombre = $e_nombre;
        $this->e_direccion = $e_direccion;
        $this->pdo = conectarBD();
    }

    // Getters
    public function getIdEmpresa(){
        return $this->id_empresa;
    }

    public function getEnombre(){
        return $this->e_nombre;
    }

    public function getEdireccion(){
        return $this->e_direccion;
    }

    // Setters
    public function setIdEmpresa($id_empresa){
        $this->id_empresa = $id_empresa;
    }

    public function setEnombre($e_nombre){
        $this->e_nombre = $e_nombre;
    }

    public function setEdireccion($e_direccion){
        $this->e_direccion = $e_direccion;
    }

    public function insertar(){
        $sql = "INSERT INTO empresa (e_nombre, e_direccion) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $resultado = $stmt->execute([$this->getEnombre(), $this->getEdireccion()]);
        if($resultado){
            $this->setIdEmpresa($this->pdo->lastInsertId());
        }
        return $resultado;
    }

    public function modificar(){
        $sql = "UPDATE empresa SET e_nombre = ?, e_direccion = ? WHERE id_empresa = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$this->getEnombre(), $this->getEdireccion(), $this->getIdEmpresa()]);
    }

    public function eliminar(){
        $sql = "DELETE FROM empresa WHERE id_empresa = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$this->getIdEmpresa()]);
    }

    public static function buscar($id){
        $pdo = conectarBD();
        $sql = "SELECT * FROM empresa WHERE id_empresa = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $fila = $stmt->fetch();
        if($fila){
            return new Empresa($fila['id_empresa'], $fila['e_nombre'], $fila['e_direccion']);
        }
        return null;
    }

    public static function listar(){
        $pdo = conectarBD();
        $sql = "SELECT * FROM empresa";
        $stmt = $pdo->query($sql);
        $empresas = [];
        while($fila = $stmt->fetch()){
            $empresas[] = new Empresa($fila['id_empresa'], $fila['e_nombre'], $fila['e_direccion']);
        }
        return $empresas;
    }

    public function __toString(){
        return "Empresa [ID: ".$this->getIdEmpresa().", Nombre: ".$this->getEnombre().", Dirección: ".$this->getEdireccion()."]";
    }
}
?>

<?php
abstract class Persona {
    protected $nombre;
    protected $apellido;
    protected $activo;

    public function __construct() {
        $this->nombre = "";
        $this->apellido = "";
        $this->activo = true;
    }

    // Getters
    public function getNombre() { return $this->nombre; }
    public function getApellido() { return $this->apellido; }
    public function getActivo() { return $this->activo; }

    // Setters
    public function setNombre($nombre) { $this->nombre = $nombre; }
    public function setApellido($apellido) { $this->apellido = $apellido; }
    public function setActivo($activo) { $this->activo = $activo; }
}
?>

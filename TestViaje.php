<?php
require_once 'Empresa.php';
require_once 'Viaje.php';
require_once 'ResponsableV.php';

$empresa = new Empresa();
$viaje = new Viaje();
$responsable = new ResponsableV();

// Insertar empresa
$empresa->insertar("Viajes Andes", "Calle 123");

// Insertar responsable
$responsable->insertar(4567, "Lucas", "Martínez");

// Insertar viaje (supone que empresa con id 1 y responsable con id 1 existen)
$viaje->insertar("Mendoza", 50, 15000, 1, 1);

// Modificar viaje con id 1
$viaje->modificar(1, "Salta", 40, 18000, 1, 1);

// Eliminar viaje
$viaje->eliminar(1);

// Eliminar empresa
$empresa->eliminar(1);

// Eliminar responsable
$responsable->eliminar(1);

echo "Todo listo.";
?>

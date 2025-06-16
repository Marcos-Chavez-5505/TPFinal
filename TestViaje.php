<?php
include_once 'Empresa.php';
include_once 'ResponsableV.php';
include_once 'Viaje.php';

class TestViajes {

    // Operaciones sobre Empresa

    public function insertarEmpresa($nombre, $direccion) {
        $empresa = new Empresa();
        $empresa->cargar($nombre, $direccion); 
        $resultado = null;
        if ($empresa->insertar()) {
            echo "Empresa insertada con éxito. ID: " . $empresa->getIdEmpresa() . "\n";
            $resultado = $empresa;
        } else {
            echo $empresa->getMensajeError() . "\n";
            $resultado = null;
        }
        return $resultado;
    }

    public function modificarEmpresa($idEmpresa, $nombre, $direccion) {
        $empresa = new Empresa();
        $resultado = false;
        if ($empresa->buscar($idEmpresa)) {
            $empresa->cargar($nombre, $direccion);
            if ($empresa->modificar()) {
                echo "Empresa modificada con éxito.\n";
                $resultado = true;
            } else {
                $empresa->getMensajeError() . "\n";
            }
        } else {
            echo "No se encontró la empresa con ID $idEmpresa\n";
        }
        return $resultado;
    }

    public function eliminarEmpresa($idEmpresa) {
        $empresa = new Empresa();
        $resultado = false;
        if ($empresa->buscar($idEmpresa)) {
            if ($empresa->eliminar()) {
                echo "Empresa eliminada con éxito.\n";
                $resultado = true;
            } else {
                $empresa->getMensajeError() . "\n";
            }
        } else {
            echo "No se encontró la empresa con ID $idEmpresa\n";
        }
        return $resultado;
    }

    // Operaciones sobre Viaje

    public function insertarViaje($destino, $cantMaxPasajeros, $importe, $idEmpresa, $numEmpleadoResponsable, $colPasajeros) {
        $resultado = false;

        $empresa = new Empresa();
        $responsable = new ResponsableV();

        if (!$empresa->buscar($idEmpresa)) {
            echo "No se encontró la empresa con ID $idEmpresa\n";
            return $resultado;
        }

        if (!$responsable->buscar($numEmpleadoResponsable)) {
            echo "No se encontró el responsable con número $numEmpleadoResponsable\n";
            return $resultado;
        }

        $viaje = new Viaje();
        $viaje->cargar(null, $destino, $cantMaxPasajeros, $importe, $empresa, $responsable, $colPasajeros);

        if ($viaje->insertar()) {
            echo "Viaje insertado con éxito. ID: " . $viaje->getIdViaje() . "\n";
            $resultado = $viaje;
        } else {
            echo $viaje->getMensajeError() . "\n";
        }
        return $resultado;
    }

    public function modificarViaje($idViaje, $destino, $cantMaxPasajeros, $importe, $idEmpresa, $numEmpleadoResponsable) {
        $resultado = false;
        $viaje = new Viaje();
        if (!$viaje->buscar($idViaje)) {
            echo "No se encontró el viaje con ID $idViaje\n";
            return $resultado;
        }

        $empresa = new Empresa();
        $responsable = new ResponsableV();

        if (!$empresa->buscar($idEmpresa)) {
            echo "No se encontró la empresa con ID $idEmpresa\n";
            return $resultado;
        }

        if (!$responsable->buscar($numEmpleadoResponsable)) {
            echo "No se encontró el responsable con número $numEmpleadoResponsable\n";
            return $resultado;
        }

        $viaje->cargar($idViaje, $destino, $cantMaxPasajeros, $importe, $empresa, $responsable);

        if ($viaje->modificar()) {
            echo "Viaje modificado con éxito.\n";
            $resultado = true;
        } else {
            echo $viaje->getMensajeError() . "\n";
        }
        return $resultado;
    }

    public function eliminarViaje($idViaje) {
        $resultado = false;
        $viaje = new Viaje();
        if ($viaje->buscar($idViaje)) {
            if ($viaje->eliminar()) {
                echo "Viaje eliminado con éxito.\n";
                $resultado = true;
            } else {
                echo $viaje->getMensajeError() . "\n";
            }
        } else {
            echo "No se encontró el viaje con ID $idViaje\n";
        }
        return $resultado;
    }

    // Operaciones sobre Responsable

    public function insertarResponsable($nroLicencia, $nombre, $apellido) {
        $resultado = null;
        $responsable = new ResponsableV();
        $responsable->cargar($nroLicencia, $nombre, $apellido);
        if ($responsable->insertar()) {
            echo "Responsable insertado con éxito. Número: " . $responsable->getIdResponsable() . "\n";
            $resultado = $responsable;
        } else {
            echo $responsable->getMensajeError() . "\n";
        }
        return $resultado;
    }
}



$test = new TestViajes();

// 1. Inserta una empresa

// $test->insertarEmpresa("Empresa XYZ", "Av. Siempre Viva 123");

// 2. Inserta un Responsable para crear una venta

// $test->insertarResponsable(123456, "Lucas", "Martinez");

// // 3. Insertar un viaje (ejecutar solo si ya existe empresa y responsable)
// $test->insertarViaje("Mar del Plata", 50, 2000, 1, 1, []);

// 4. Modificar un viaje
// $test->modificarViaje(1, "Villa Gesell", 40, 1800, 1, 1);

// 5. Eliminar viaje
// $test->eliminarViaje(1);

// 6. Eliminar empresa (tené cuidado con integridad referencial)
// $test->eliminarEmpresa(1);



?>



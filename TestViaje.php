<?php
include_once 'Empresa.php';
include_once 'ResponsableV.php';
include_once 'Viaje.php';



class TestViajes {

    // Operaciones sobre Empresa

    public function verEmpresas() {
        $empresa = new Empresa();
        $empresas = $empresa->listar();
        $resultado = "";

        if ($empresas === null || count($empresas) === 0) {
            $resultado = "No hay empresas registradas.<br>";
        } else {
            foreach ($empresas as $e) {
                $resultado .= "ID: " . $e->getIdEmpresa() . " | Nombre: " . $e->getNombre() . " | Dirección: " . $e->getDireccion() . "<br>";
            }
        }

        return $resultado;
    }

    public function insertarEmpresa($nombre, $direccion) {
        $empresa = new Empresa();
        $empresa->cargar($nombre, $direccion); 
        $resultado = null;
        if ($empresa->insertar()) {
            echo "Empresa insertada con éxito. ID: " . $empresa->getIdEmpresa() . "<br>";
            $resultado = $empresa;
        } else {
            echo $empresa->getMensajeError() . "<br>";
        }
        return $resultado;
    }

    public function modificarEmpresa($idEmpresa, $nombre, $direccion) {
        $empresa = new Empresa();
        $resultado = false;
        if ($empresa->buscar($idEmpresa)) {
            $empresa->cargar($nombre, $direccion);
            if ($empresa->modificar()) {
                echo "Empresa modificada con éxito.<br>";
                $resultado = true;
            } else {
                echo $empresa->getMensajeError() . "<br>";
            }
        } else {
            echo "No se encontró la empresa con ID $idEmpresa<br>";
        }
        return $resultado;
    }

    public function eliminarEmpresa($idEmpresa) {
        $empresa = new Empresa();
        $resultado = false;
        if ($empresa->buscar($idEmpresa)) {
            if ($empresa->eliminar()) {
                echo "Empresa eliminada con éxito.<br>";
                $resultado = true;
            } else {
                echo $empresa->getMensajeError() . "<br>";
            }
        } else {
            echo "No se encontró la empresa con ID $idEmpresa<br>";
        }
        return $resultado;
    }

    // Operaciones sobre Viaje

    public function verViajes() {
        $viaje = new Viaje();
        $viajes = $viaje->listar();
        $resultado = "";

        if ($viajes === null || count($viajes) === 0) {
            echo "No hay viajes registrados.<br>";
        } else {
            foreach ($viajes as $v) {
                $resultado .= "ID: " . $v->getIdViaje() . " | Destino: " . $v->getDestino() . " | Cantidad Máxima: " . $v->getCantMaxPasajeros() . " | Importe: " . $v->getImporte() . " | " . $v->getEmpresa() . " | " . $v->getResponsable() ."<br>";
            }
        }

        return $resultado;
    }

    public function insertarViaje($destino, $cantMaxPasajeros, $importe, $idEmpresa, $numEmpleadoResponsable, $colPasajeros) {
        $resultado = false;
        $encontrado = true;

        $empresa = new Empresa();
        $responsable = new ResponsableV();

        if (!$empresa->buscar($idEmpresa)) {
            $mensajeErrorBuscar = "No se encontró la empresa con ID $idEmpresa<br>";
            $encontrado = false;
        }

        if (!$responsable->buscar($numEmpleadoResponsable)) {
            $mensajeErrorBuscar = "No se encontró el responsable con número $numEmpleadoResponsable<br>";
            $encontrado = false;
        }

        $viaje = new Viaje();
        $viaje->cargar(null, $destino, $cantMaxPasajeros, $importe, $empresa, $responsable, $colPasajeros);

        if ($viaje->insertar() && $encontrado) {
            echo "Viaje insertado con éxito. ID: " . $viaje->getIdViaje() . "<br>";
            $resultado = $viaje;
        } else {
            echo $mensajeErrorBuscar;
            echo $viaje->getMensajeError() . "<br>";
        }
        return $resultado;
    }

    public function modificarViaje($idViaje, $destino, $cantMaxPasajeros, $importe, $idEmpresa, $numEmpleadoResponsable) {
        $resultado = false;
        $viaje = new Viaje();
        if (!$viaje->buscar($idViaje)) {
            echo "No se encontró el viaje con ID $idViaje<br>";
            return $resultado;
        }

        $empresa = new Empresa();
        $responsable = new ResponsableV();

        if (!$empresa->buscar($idEmpresa)) {
            echo "No se encontró la empresa con ID $idEmpresa<br>";
            return $resultado;
        }

        if (!$responsable->buscar($numEmpleadoResponsable)) {
            echo "No se encontró el responsable con número $numEmpleadoResponsable<br>";
            return $resultado;
        }

        $viaje->cargar($idViaje, $destino, $cantMaxPasajeros, $importe, $empresa, $responsable);

        if ($viaje->modificar()) {
            echo "Viaje modificado con éxito.<br>";
            $resultado = true;
        } else {
            echo $viaje->getMensajeError() . "<br>";
        }
        return $resultado;
    }

    public function eliminarViaje($idViaje) {
        $resultado = false;
        $viaje = new Viaje();
        if ($viaje->buscar($idViaje)) {
            if ($viaje->eliminar()) {
                echo "Viaje eliminado con éxito.<br>";
                $resultado = true;
            } else {
                echo $viaje->getMensajeError() . "<br>";
            }
        } else {
            echo "No se encontró el viaje con ID $idViaje<br>";
        }
        return $resultado;
    }


    // Operaciones sobre Responsable

    public function verResponsables() {
        $responsable = new ResponsableV();
        $responsables = $responsable->listar();
        $resultado = "";

        if ($responsables === null || count($responsables) === 0) {
            $resultado = "No hay responsables registrados.<br>";
        } else {
            foreach ($responsables as $r) {
                $resultado .= "Responsable Nº: " . $r->getIdResponsable() . " | Licencia: " . $r->getNumLicencia() . " | Nombre: " . $r->getNombre() . " " . $r->getApellido() . "<br>";
            }
        }

        return $resultado;
    }

    public function insertarResponsable($nroLicencia, $nombre, $apellido) {
        $resultado = null;
        $responsable = new ResponsableV();
        $responsable->cargar($nroLicencia, $nombre, $apellido);
        if ($responsable->insertar()) {
            echo "Responsable insertado con éxito. Número: " . $responsable->getIdResponsable() . "<br>";
            $resultado = $responsable;
        } else {
            echo $responsable->getMensajeError() . "<br>";
        }
        return $resultado;
    }

 
}

    



$test = new TestViajes();

// 1. Inserta una empresa

// $test->insertarEmpresa("Empresa XYZ", "Av. Siempre Viva 123");
// echo "Listado Empresas:<br>" . $test->verEmpresas()

// 2. Inserta un Responsable para crear una venta

// $test->insertarResponsable(123456, "Lucas", "Martinez");
// echo "Listado Responsables:<br>" . $test->verResponsables()

// 3. Insertar un viaje (ejecutar solo si ya existe empresa y responsable)
// $test->insertarViaje("Mar del Plata", 50, 2000, 2, 1, []);
// echo "Listado Viajes: <br>" . $test->verViajes(); 

// 4. Modificar un viaje
// $test->modificarViaje(4, "Villa 61", 40, 1800, 2, 1);
// echo "Listado Viajes: <br>" . $test->verViajes();

// 5. Eliminar viaje
// $test->eliminarViaje(2);
// echo "Listado Viajes: <br>" . $test->verViajes();

// 6. Eliminar empresa (tené cuidado con integridad referencial)
// $test->eliminarEmpresa(1);


?>



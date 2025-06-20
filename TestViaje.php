<?php
include_once 'Empresa.php';
include_once 'ResponsableV.php';
include_once 'Viaje.php';
include_once 'Pasajero.php';

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
            $empresa->setIdEmpresa($idEmpresa);
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
            $resultado = "No hay viajes registrados.<br>";
        } else {
            foreach ($viajes as $v) {
                $resultado .= "ID: " . $v->getIdViaje() . " | Destino: " . $v->getDestino() .
                " | Cantidad Máxima: " . $v->getCantMaxPasajeros() .
                " | Importe: " . $v->getImporte() .
                " | Empresa: " . $v->getEmpresa()->getNombre() .
                " | Responsable: " . $v->getResponsable()->getNombre() . " " . $v->getResponsable()->getApellido() . "<br>" .
                "Colección Pasajeros: <br>" . nl2br($v->ColPasajerosStr()) . "<br><br>";
            }
        }

        return $resultado;
    }

    public function insertarViaje($destino, $cantMaxPasajeros, $importe, $idEmpresa, $numEmpleadoResponsable, $colPasajeros = []) {
        $resultado = false;
        $encontrado = true;
        $mensajeErrorBuscar = "";

        $empresa = new Empresa();
        $responsable = new ResponsableV();

        if (!$empresa->buscar($idEmpresa)) {
            $mensajeErrorBuscar .= "No se encontró la empresa con ID $idEmpresa<br>";
            $encontrado = false;
        }

        if (!$responsable->buscar($numEmpleadoResponsable)) {
            $mensajeErrorBuscar .= "No se encontró el responsable con número $numEmpleadoResponsable<br>";
            $encontrado = false;
        }

        $viaje = new Viaje();
        $viaje->cargar(null, $destino, $cantMaxPasajeros, $importe, $empresa, $responsable, $colPasajeros);

        if ($encontrado && $viaje->insertar()) {
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
        } else {
            $empresa = new Empresa();
            $responsable = new ResponsableV();

            if (!$empresa->buscar($idEmpresa)) {
                echo "No se encontró la empresa con ID $idEmpresa<br>";
            } elseif (!$responsable->buscar($numEmpleadoResponsable)) {
                echo "No se encontró el responsable con número $numEmpleadoResponsable<br>";
            } else {
                $viaje->cargar($idViaje, $destino, $cantMaxPasajeros, $importe, $empresa, $responsable);
                if ($viaje->modificar()) {
                    echo "Viaje modificado con éxito.<br>";
                    $resultado = true;
                } else {
                    echo $viaje->getMensajeError() . "<br>";
                }
            }
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

    // Operaciones sobre Pasajero

    public function verPasajeros() {
        $pasajero = new Pasajero();
        $pasajeros = $pasajero->listar();
        $resultado = "";

        if ($pasajeros === null || count($pasajeros) === 0) {
            $resultado = "No hay pasajeros registrados.<br>";
        } else {
            foreach ($pasajeros as $p) {
                $resultado .= "Documento: " . $p->getDocumento() . " | Nombre: " . $p->getNombre() . " " . $p->getApellido() . " | Teléfono: " . $p->getTelefono() . "<br>";
            }
        }
        return $resultado;
    }

    public function insertarPasajero($documento, $nombre, $apellido, $telefono, $idViaje) {
        $resultado = null;
        $viaje = new Viaje();

        if ($viaje->buscar($idViaje)) {
            $pasajero = new Pasajero();
            $pasajero->cargar($documento, $nombre, $apellido, $telefono);
            if ($pasajero->insertar()) {
                $pasajero->agregarViaje($idViaje);
                echo "Pasajero insertado con éxito. Documento: " . $documento . "<br>";
                $resultado = $pasajero;
            } else {
                echo $pasajero->getMensajeError() . "<br>";
            }
        } else {
            echo "No se encontró el viaje con ID: $idViaje<br>";
        }

        return $resultado;
    }
}

// Ejemplo de uso
$test = new TestViajes();

// $test->insertarEmpresa("Aerolineas Argentinas", "Av. Argentina 123");
// $test->insertarResponsable(123456, "Lucas", "Martinez");
// $test->insertarViaje("Mar del Pepe", 50, 2000, 1, 1);
// $test->insertarPasajero("48294", "Marza", "Chavi", "2995565790", 1);
// $test->eliminarViaje(1);
// echo $test->verViajes();
// echo $test->verPasajeros();
?>


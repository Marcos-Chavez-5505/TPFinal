<?php
include_once 'Empresa.php';
include_once 'ResponsableV.php';
include_once 'Viaje.php';
include_once 'Pasajero.php';
include_once 'Persona.php';
include_once 'Participa.php'; // Para la tabla participa

class TestViajes {

    // Personas
    public function insertarPersona($documento, $nombre, $apellido) {
        $persona = new Persona();
        $persona->cargar($documento, $nombre, $apellido);
        $respuesta = false;

        if ($persona->insertar()) {
            echo "Persona insertada con éxito. Documento: $documento\n";
            $respuesta = true;
        } else {
            echo "Error insertar persona: " . $persona->getMensajeError() . "\n";
        }

        return $respuesta;
    }

    public function eliminarPersona($documento) {
        $respuesta = false;
        $persona = new Persona();

        if ($persona->buscar($documento)) {
            if ($persona->eliminar()) {
                echo "Persona eliminada con éxito. Documento: $documento\n";
                $respuesta = true;
            } else {
                echo "Error eliminar persona: " . $persona->getMensajeError() . "\n";
            }
        } else {
            echo "No se encontró la persona con documento $documento\n";
        }

        return $respuesta;
    }

    public function verPersonas() {
        $persona = new Persona();
        $personas = $persona->listar();
        $resultado = "No hay personas registradas.\n";

        if (!empty($personas)) {
            $resultado = "";
            foreach ($personas as $p) {
                $resultado .= "Documento: " . $p->getDocumento() . " | Nombre: " . $p->getNombre() . " " . $p->getApellido() . "\n";
            }
        }

        return $resultado;
    }

    // Empresas
    public function insertarEmpresa($nombre, $direccion) {
        $empresa = new Empresa();
        $empresa->cargar($nombre, $direccion);
        $respuesta = null;

        if ($empresa->insertar()) {
            echo "Empresa insertada con éxito. ID: " . $empresa->getIdEmpresa() . "\n";
            $respuesta = $empresa;
        } else {
            echo "Error insertar empresa: " . $empresa->getMensajeError() . "\n";
        }

        return $respuesta;
    }

    public function eliminarEmpresa($idEmpresa) {
        $respuesta = false;
        $empresa = new Empresa();

        if ($empresa->buscar($idEmpresa)) {
            if ($empresa->eliminar()) {
                echo "Empresa eliminada con éxito. ID: $idEmpresa\n";
                $respuesta = true;
            } else {
                echo "Error eliminar empresa: " . $empresa->getMensajeError() . "\n";
            }
        } else {
            echo "No se encontró la empresa con ID $idEmpresa\n";
        }

        return $respuesta;
    }

    public function verEmpresas() {
        $empresa = new Empresa();
        $empresas = $empresa->listar();
        $resultado = "No hay empresas registradas.\n";

        if (!empty($empresas)) {
            $resultado = "";
            foreach ($empresas as $e) {
                $resultado .= "ID: " . $e->getIdEmpresa() . " | Nombre: " . $e->getNombre() . " | Dirección: " . $e->getDireccion() . "\n";
            }
        }

        return $resultado;
    }

    // Viajes
    public function insertarViaje($destino, $cantMaxPasajeros, $importe, $idEmpresa, $documentoResponsable, $colPasajeros = []) {
        $resultado = null;

        $empresa = new Empresa();
        if ($empresa->buscar($idEmpresa)) {
            $responsable = new ResponsableV();
            if ($responsable->buscar($documentoResponsable)) {
                $viaje = new Viaje();
                $viaje->cargar($destino, $cantMaxPasajeros, $importe, $empresa, $responsable);

                if ($viaje->insertar()) {
                    echo "Viaje insertado con éxito. ID: " . $viaje->getIdViaje() . "\n";
                    $resultado = $viaje;
                } else {
                    echo "Error insertar viaje: " . $viaje->getMensajeError() . "\n";
                }
            } else {
                echo "No se encontró el responsable con documento $documentoResponsable\n";
            }
        } else {
            echo "No se encontró la empresa con ID $idEmpresa\n";
        }

        return $resultado;
    }

    public function eliminarViaje($idViaje) {
        $respuesta = false;
        $viaje = new Viaje();

        if ($viaje->buscar($idViaje)) {
            if ($viaje->eliminar()) {
                echo "Viaje eliminado con éxito. ID: $idViaje\n";
                $respuesta = true;
            } else {
                echo "Error eliminar viaje: " . $viaje->getMensajeError() . "\n";
            }
        } else {
            echo "No se encontró el viaje con ID $idViaje\n";
        }

        return $respuesta;
    }

    public function verViajes() {
        $viaje = new Viaje();
        $viajes = $viaje->listar();
        $resultado = "No hay viajes registrados.\n";

        if (!empty($viajes)) {
            $resultado = "";
            foreach ($viajes as $v) {
                $resultado .= "ID: " . $v->getIdViaje() . " | Destino: " . $v->getDestino() .
                    " | Cantidad Máxima: " . $v->getCantMaxPasajeros() .
                    " | Importe: " . $v->getImporte() .
                    " | Empresa: " . $v->getEmpresa()->getNombre() .
                    " | Responsable: " . $v->getResponsable()->getNombre() . " " . $v->getResponsable()->getApellido() . "\n" .
                    "Colección Pasajeros: \n" . $v->ColPasajerosStr() . "\n\n";
            }
        }

        return $resultado;
    }

    // Responsable
    public function asignarResponsable($documento, $licencia) {
        $respuesta = false;
        $responsable = new ResponsableV();

        if ($responsable->asignarComoResponsable($documento, $licencia)) {
            echo "Responsable asignado con éxito. Documento: $documento\n";
            $respuesta = true;
        } else {
            echo "Error asignar responsable: " . $responsable->getMensajeError() . "\n";
        }

        return $respuesta;
    }

    public function eliminarResponsable($documento) {
        $respuesta = false;
        $responsable = new ResponsableV();

        if ($responsable->buscar($documento)) {
            if ($responsable->eliminar()) {
                echo "Responsable eliminado con éxito. Documento: $documento\n";
                $respuesta = true;
            } else {
                echo "Error eliminar responsable: " . $responsable->getMensajeError() . "\n";
            }
        } else {
            echo "No se encontró el responsable con documento $documento\n";
        }

        return $respuesta;
    }

    public function verResponsables() {
        $responsable = new ResponsableV();
        $responsables = $responsable->listar(" WHERE r.activo = TRUE");
        $resultado = "No hay responsables activos registrados.\n";

        if (!empty($responsables)) {
            $resultado = "";
            foreach ($responsables as $r) {
                $resultado .= "Responsable Documento: " . $r->getDocumento() .
                    " | Licencia: " . $r->getNumLicencia() .
                    " | Nombre: " . $r->getNombre() . " " . $r->getApellido() . "\n";
            }
        }

        return $resultado;
    }

    public function asignarPasajero($documento, $telefono, $idViaje) {
        $resultado = false;
        $mensajeError = "";

        $viaje = new Viaje();
        $participa = new Participa();
        $pasajero = new Pasajero();

        if (!$viaje->buscar($idViaje)) {
            $mensajeError = "Error: El viaje con ID $idViaje no existe.\n";
        } elseif ($participa->buscar($idViaje, $documento, true)) {
            $mensajeError = "Error: El pasajero con documento $documento ya está registrado en este viaje.\n";
        } elseif ($pasajero->buscar($documento) || $pasajero->asignarComoPasajero($documento, $telefono)) {
            $participa->setIdViaje($idViaje);
            $participa->setDocumento($documento);

            if ($participa->insertar()) {
                $resultado = true;
                $mensajeError = "Pasajero asignado con éxito al viaje.\n";
            } else {
                $mensajeError = "Error al vincular al viaje: " . $participa->getMensajeError() . "\n";
            }
        } else {
            $mensajeError = "Error al registrar pasajero: " . $pasajero->getMensajeError() . "\n";
        }

        echo $mensajeError;
        return $resultado;
    }

    public function eliminarPasajero($documento) {
        $respuesta = false;
        $pasajero = new Pasajero();

        if ($pasajero->buscar($documento)) {
            if ($pasajero->eliminar()) {
                echo "Pasajero eliminado con éxito. Documento: $documento\n";
                $respuesta = true;
            } else {
                echo "Error eliminar pasajero: " . $pasajero->getMensajeError() . "\n";
            }
        } else {
            echo "No se encontró el pasajero con documento $documento\n";
        }

        return $respuesta;
    }

    public function verPasajeros() {
        $pasajero = new Pasajero();
        $pasajeros = $pasajero->listar(" WHERE p.activo = TRUE");
        $resultado = "No hay pasajeros activos registrados.\n";

        if (!empty($pasajeros)) {
            $resultado = "";
            foreach ($pasajeros as $p) {
                $resultado .= "Pasajero Documento: " . $p->getDocumento() .
                    " | Teléfono: " . $p->getTelefono() .
                    " | Nombre: " . $p->getNombre() . " " . $p->getApellido() . "\n";
            }
        }

        return $resultado;
    }

    public function reactivarViaje($idViaje) {
        $viaje = new Viaje();
        $viaje->setIdViaje($idViaje);

        if ($viaje->reactivar()) {
            echo "Viaje con ID {$viaje->getIdViaje()} reactivado correctamente\n";
        } else {
            echo "Error al reactivar el viaje con ID {$viaje->getIdViaje()}: " . $viaje->getMensajeError() . "\n";
        }
    }
}

// Menú principal
$test = new TestViajes();

do {
    echo "\n*** Menú de Gestión de Viajes ***\n";
    echo "1. Insertar Persona\n";
    echo "2. Ver Personas\n";
    echo "3. Eliminar Persona\n";
    echo "4. Insertar Empresa\n";
    echo "5. Ver Empresas\n";
    echo "6. Eliminar Empresa\n";
    echo "7. Insertar Viaje\n";
    echo "8. Ver Viajes\n";
    echo "9. Eliminar Viaje\n";
    echo "10. Asignar Responsable\n";
    echo "11. Asignar Pasajero a Viaje\n";
    echo "12. Reactivar Viaje\n";
    echo "0. Salir\n";
    echo "Seleccione una opción: ";
    $op = trim(fgets(STDIN));

    switch ($op) {
        case 1:
            echo "Documento: "; $doc = trim(fgets(STDIN));
            echo "Nombre: "; $nom = trim(fgets(STDIN));
            echo "Apellido: "; $ape = trim(fgets(STDIN));
            $test->insertarPersona($doc, $nom, $ape);
            break;

        case 2:
            echo $test->verPersonas();
            break;

        case 3:
            echo "Documento a eliminar: "; $doc = trim(fgets(STDIN));
            $test->eliminarPersona($doc);
            break;

        case 4:
            echo "Nombre Empresa: "; $nom = trim(fgets(STDIN));
            echo "Dirección: "; $dir = trim(fgets(STDIN));
            $test->insertarEmpresa($nom, $dir);
            break;

        case 5:
            echo $test->verEmpresas();
            break;

        case 6:
            echo "ID Empresa a eliminar: "; $id = trim(fgets(STDIN));
            $test->eliminarEmpresa($id);
            break;

        case 7:
            echo "Destino: "; $destino = trim(fgets(STDIN));
            echo "Cant. Máxima Pasajeros: "; $max = trim(fgets(STDIN));
            echo "Importe: "; $importe = trim(fgets(STDIN));
            echo "ID Empresa: "; $idEmpresa = trim(fgets(STDIN));
            echo "Documento Responsable: "; $docResp = trim(fgets(STDIN));
            $test->insertarViaje($destino, $max, $importe, $idEmpresa, $docResp);
            break;

        case 8:
            echo $test->verViajes();
            break;

        case 9:
            echo "ID Viaje a eliminar: "; $id = trim(fgets(STDIN));
            $test->eliminarViaje($id);
            break;

        case 10:
            echo "Documento: "; $doc = trim(fgets(STDIN));
            echo "Número Licencia: "; $lic = trim(fgets(STDIN));
            $test->asignarResponsable($doc, $lic);
            break;

        case 11:
            echo "Documento Pasajero: "; $doc = trim(fgets(STDIN));
            echo "Teléfono: "; $tel = trim(fgets(STDIN));
            echo "ID Viaje: "; $idViaje = trim(fgets(STDIN));
            $test->asignarPasajero($doc, $tel, $idViaje);
            break;

        case 12:
            echo "ID Viaje a reactivar: "; $id = trim(fgets(STDIN));
            $test->reactivarViaje($id);
            break;

        case 0:
            echo "Saliendo...\n";
            break;

        default:
            echo "Opción inválida.\n";
    }
} while ($op != 0);

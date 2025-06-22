<?php
include_once 'Empresa.php';
include_once 'ResponsableV.php';
include_once 'Viaje.php';
include_once 'Pasajero.php';
include_once 'Persona.php';

class TestViajes {

    // Personas
    public function insertarPersona($documento, $nombre, $apellido) {
        $persona = new Persona();
        $persona->cargar($documento, $nombre, $apellido); // Documento sí va acá
        if ($persona->insertar()) {
            echo "Persona insertada con éxito. Documento: $documento<br>";
            return true;
        } else {
            echo "Error insertar persona: " . $persona->getMensajeError() . "<br>";
            return false;
        }
    }

    public function eliminarPersona($documento) {
        $persona = new Persona();
        if ($persona->buscar($documento)) {
            if ($persona->eliminar()) {
                echo "Persona eliminada con éxito. Documento: $documento<br>";
                return true;
            } else {
                echo "Error eliminar persona: " . $persona->getMensajeError() . "<br>";
            }
        } else {
            echo "No se encontró la persona con documento $documento<br>";
        }
        return false;
    }

    public function verPersonas() {
        $persona = new Persona();
        $personas = $persona->listar();
        if (empty($personas)) {
            return "No hay personas registradas.<br>";
        }
        $resultado = "";
        foreach ($personas as $p) {
            $resultado .= "Documento: " . $p->getDocumento() . " | Nombre: " . $p->getNombre() . " " . $p->getApellido() . "<br>";
        }
        return $resultado;
    }

    // Empresas
    public function insertarEmpresa($nombre, $direccion) {
        $empresa = new Empresa();
        $empresa->cargar($nombre, $direccion); // SIN ID aquí
        if ($empresa->insertar()) {
            echo "Empresa insertada con éxito. ID: " . $empresa->getIdEmpresa() . "<br>";
            return $empresa;
        } else {
            echo "Error insertar empresa: " . $empresa->getMensajeError() . "<br>";
            return null;
        }
    }

    public function eliminarEmpresa($idEmpresa) {
        $empresa = new Empresa();
        if ($empresa->buscar($idEmpresa)) {
            if ($empresa->eliminar()) {
                echo "Empresa eliminada con éxito. ID: $idEmpresa<br>";
                return true;
            } else {
                echo "Error eliminar empresa: " . $empresa->getMensajeError() . "<br>";
            }
        } else {
            echo "No se encontró la empresa con ID $idEmpresa<br>";
        }
        return false;
    }

    public function verEmpresas() {
        $empresa = new Empresa();
        $empresas = $empresa->listar();
        if (empty($empresas)) {
            return "No hay empresas registradas.<br>";
        }
        $resultado = "";
        foreach ($empresas as $e) {
            $resultado .= "ID: " . $e->getIdEmpresa() . " | Nombre: " . $e->getNombre() . " | Dirección: " . $e->getDireccion() . "<br>";
        }
        return $resultado;
    }

    // Viajes
    public function insertarViaje($destino, $cantMaxPasajeros, $importe, $idEmpresa, $documentoResponsable, $colPasajeros = []) {
        $empresa = new Empresa();
        if (!$empresa->buscar($idEmpresa)) {
            echo "No se encontró la empresa con ID $idEmpresa<br>";
            return false;
        }

        $responsable = new ResponsableV();
        if (!$responsable->buscar($documentoResponsable)) {
            echo "No se encontró el responsable con documento $documentoResponsable<br>";
            return false;
        }

        $viaje = new Viaje();
        $viaje->cargar($destino, $cantMaxPasajeros, $importe, $empresa, $responsable); // SIN ID aquí

        if ($viaje->insertar()) {
            echo "Viaje insertado con éxito. ID: " . $viaje->getIdViaje() . "<br>";
            return $viaje;
        } else {
            echo "Error insertar viaje: " . $viaje->getMensajeError() . "<br>";
            return false;
        }
    }

    public function eliminarViaje($idViaje) {
        $viaje = new Viaje();
        if ($viaje->buscar($idViaje)) {
            if ($viaje->eliminar()) {
                echo "Viaje eliminado con éxito. ID: $idViaje<br>";
                return true;
            } else {
                echo "Error eliminar viaje: " . $viaje->getMensajeError() . "<br>";
            }
        } else {
            echo "No se encontró el viaje con ID $idViaje<br>";
        }
        return false;
    }

    public function verViajes() {
        $viaje = new Viaje();
        $viajes = $viaje->listar();
        if (empty($viajes)) {
            return "No hay viajes registrados.<br>";
        }
        $resultado = "";
        foreach ($viajes as $v) {
            $resultado .= "ID: " . $v->getIdViaje() . " | Destino: " . $v->getDestino() .
                " | Cantidad Máxima: " . $v->getCantMaxPasajeros() .
                " | Importe: " . $v->getImporte() .
                " | Empresa: " . $v->getEmpresa()->getNombre() .
                " | Responsable: " . $v->getResponsable()->getNombre() . " " . $v->getResponsable()->getApellido() . "<br>" .
                "Colección Pasajeros: <br>" . nl2br($v->ColPasajerosStr()) . "<br><br>";
        }
        return $resultado;
    }

    // Responsable
    public function asignarResponsable($documento, $licencia) {
        $responsable = new ResponsableV();
        if ($responsable->asignarComoResponsable($documento, $licencia)) {
            echo "Responsable asignado con éxito. Documento: $documento<br>";
            return true;
        } else {
            echo "Error asignar responsable: " . $responsable->getMensajeError() . "<br>";
            return false;
        }
    }

    public function eliminarResponsable($documento) {
        $responsable = new ResponsableV();
        if ($responsable->buscar($documento)) {
            if ($responsable->eliminar()) {
                echo "Responsable eliminado con éxito. Documento: $documento<br>";
                return true;
            } else {
                echo "Error eliminar responsable: " . $responsable->getMensajeError() . "<br>";
            }
        } else {
            echo "No se encontró el responsable con documento $documento<br>";
        }
        return false;
    }

    public function verResponsables() {
        $responsable = new ResponsableV();
        $responsables = $responsable->listar(" WHERE r.activo = TRUE"); // Filtramos solo activos
        
        if (empty($responsables)) {
            return "No hay responsables activos registrados.<br>";
        }
        
        $resultado = "";
        foreach ($responsables as $r) {
            $resultado .= "Responsable Documento: " . $r->getDocumento() . 
                        " | Licencia: " . $r->getNumLicencia() .
                        " | Nombre: " . $r->getNombre() . " " . $r->getApellido() . "<br>";
        }
        
        return $resultado;
    }

    public function asignarPasajero($documento, $telefono, $idViaje) {
    $resultado = false;
    $mensajeError = "";

    // 1. Verificar si el viaje existe
    $viaje = new Viaje();
    $participa = new Participa();
    $pasajero = new Pasajero();
    
    if ($viaje->buscar($idViaje)) {
        // 2. Verificar si el pasajero ya está asignado al viaje
        $participa->setIdViaje($idViaje);
        $participa->setDocumento($documento);
        
        if (!$participa->buscar($idViaje, $documento, true)) {
            // 3. Crear/verificar pasajero
            if ($pasajero->buscar($documento) || $pasajero->asignarComoPasajero($documento, $telefono)) {
                // 4. Asignar al viaje (participa)
                if ($participa->insertar()) {
                    $resultado = true;
                    $mensajeError = "Pasajero asignado con éxito al viaje.<br>";
                } else {
                    $mensajeError = "Error al vincular al viaje: " . $participa->getMensajeError() . "<br>";
                }
            } else {
                $mensajeError = "Error al registrar pasajero: " . $pasajero->getMensajeError() . "<br>";
            }
        } else {
            $mensajeError = "Error: El pasajero ya está registrado en este viaje<br>";
        }
    } else {
        $mensajeError = "Error: El viaje con ID $idViaje no existe<br>";
    }

        echo $mensajeError;
        return $resultado;
    }

    public function eliminarPasajero($documento) {
        $pasajero = new Pasajero();
        if ($pasajero->buscar($documento)) {
            if ($pasajero->eliminar()) {
                echo "Pasajero eliminado con éxito. Documento: $documento<br>";
                return true;
            } else {
                echo "Error eliminar pasajero: " . $pasajero->getMensajeError() . "<br>";
            }
        } else {
            echo "No se encontró el pasajero con documento $documento<br>";
        }
        return false;
    }

    public function verPasajeros() {
        $pasajero = new Pasajero();
        $pasajeros = $pasajero->listar(" WHERE p.activo = TRUE"); // Filtramos solo activos
        
        if (empty($pasajeros)) {
            return "No hay pasajeros activos registrados.<br>";
        }
        
        $resultado = "";
        foreach ($pasajeros as $p) {
            $resultado .= "Pasajero Documento: " . $p->getDocumento() . 
                        " | Teléfono: " . $p->getTelefono() . 
                        " | Nombre: " . $p->getNombre() . " " . $p->getApellido() . "<br>";
        }
        
        return $resultado;
    }
}


// Ejemplo de uso completo con orden correcto
$test = new TestViajes();



// 1. Insertar persona
// $test->insertarPersona("2", "Lucas", "Peroncha");

// 2. Asignar responsable (dni, num_licencia)
// $test->asignarResponsable("2", 12345);

// 3. Insertar empresa 
// $empresa = $test->insertarEmpresa("Aerolineas Argentinas", "Av. Argentina 123");

// $test->eliminarEmpresa(1);



// 4. Insertar viaje con el responsable y empresa creados

// $test->insertarViaje("Mar del Pepe", 50, 2000, 1, "2");


// Asignar pasajero (doc, tel, id_viaje)
// $test->asignarPasajero("2", 29998889, 1);


// echo $test->verPasajeros();

// Borrado logico
// $test->eliminarViaje(1);

// 5. Mostrar viajes
// echo $test->verViajes();

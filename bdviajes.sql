-- Tabla empresa
CREATE TABLE empresa (
    id_empresa BIGINT AUTO_INCREMENT,
    e_nombre VARCHAR(150),
    e_direccion VARCHAR(150),
    activo BOOLEAN DEFAULT TRUE,
    PRIMARY KEY (id_empresa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

-- Tabla responsable
CREATE TABLE responsable (
    r_numeroempleado BIGINT AUTO_INCREMENT,
    r_numerolicencia BIGINT,
    r_nombre VARCHAR(150), 
    r_apellido VARCHAR(150), 
    activo BOOLEAN DEFAULT TRUE,
    PRIMARY KEY (r_numeroempleado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

-- Tabla viaje
CREATE TABLE viaje (
    id_viaje BIGINT AUTO_INCREMENT,              -- Código de viaje
    v_destino VARCHAR(150),
    v_cantmaxpasajeros INT,
    v_importe FLOAT,
    id_empresa BIGINT,
    r_numeroempleado BIGINT,
    activo BOOLEAN DEFAULT TRUE,
    PRIMARY KEY (id_viaje),
    FOREIGN KEY (id_empresa) REFERENCES empresa(id_empresa)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    FOREIGN KEY (r_numeroempleado) REFERENCES responsable(r_numeroempleado)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

-- Tabla pasajero (sin id_viaje, relación N:M manejada por tabla intermedia)
CREATE TABLE pasajero (
    p_documento VARCHAR(15),
    p_nombre VARCHAR(150), 
    p_apellido VARCHAR(150), 
    p_telefono VARCHAR(20),
    activo BOOLEAN DEFAULT TRUE,
    PRIMARY KEY (p_documento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla intermedia participa para relación N:M entre viajes y pasajeros
CREATE TABLE participa (
    id_viaje BIGINT,
    p_documento VARCHAR(15),
    activo BOOLEAN DEFAULT TRUE,
    PRIMARY KEY (id_viaje, p_documento),
    FOREIGN KEY (id_viaje) REFERENCES viaje(id_viaje)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    FOREIGN KEY (p_documento) REFERENCES pasajero(p_documento)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Índices para optimizar consultas filtradas por activo
CREATE INDEX idx_empresa_activo ON empresa(activo);
CREATE INDEX idx_responsable_activo ON responsable(activo);
CREATE INDEX idx_viaje_activo ON viaje(activo);
CREATE INDEX idx_pasajero_activo ON pasajero(activo);
CREATE INDEX idx_participa_activo ON participa(activo);

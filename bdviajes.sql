-- Tabla empresa
CREATE TABLE empresa (
    id_empresa BIGINT AUTO_INCREMENT,
    e_nombre VARCHAR(150),
    e_direccion VARCHAR(150),
    activo BOOLEAN DEFAULT TRUE,
    PRIMARY KEY (id_empresa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

-- Tabla persona (superclase)
CREATE TABLE persona (
    documento VARCHAR(15),
    nombre VARCHAR(150),
    apellido VARCHAR(150),
    activo BOOLEAN DEFAULT TRUE,
    PRIMARY KEY (documento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla responsable (subclase) con borrado lógico
CREATE TABLE responsable (
    documento VARCHAR(15),
    r_numerolicencia BIGINT,
    activo BOOLEAN DEFAULT TRUE,
    PRIMARY KEY (documento),
    FOREIGN KEY (documento) REFERENCES persona(documento)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla pasajero (subclase) con borrado lógico
CREATE TABLE pasajero (
    documento VARCHAR(15),
    p_telefono VARCHAR(20),
    activo BOOLEAN DEFAULT TRUE,
    PRIMARY KEY (documento),
    FOREIGN KEY (documento) REFERENCES persona(documento)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla viaje
CREATE TABLE viaje (
    id_viaje BIGINT AUTO_INCREMENT,
    v_destino VARCHAR(150),
    v_cantmaxpasajeros INT,
    v_importe FLOAT,
    id_empresa BIGINT,
    documento_responsable VARCHAR(15),
    activo BOOLEAN DEFAULT TRUE,
    PRIMARY KEY (id_viaje),
    FOREIGN KEY (id_empresa) REFERENCES empresa(id_empresa)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    FOREIGN KEY (documento_responsable) REFERENCES responsable(documento)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

-- Tabla participa (relación N:M entre viaje y pasajero)
CREATE TABLE participa (
    id_viaje BIGINT,
    documento VARCHAR(15),
    activo BOOLEAN DEFAULT TRUE,
    PRIMARY KEY (id_viaje, documento),
    FOREIGN KEY (id_viaje) REFERENCES viaje(id_viaje)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    FOREIGN KEY (documento) REFERENCES pasajero(documento)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Índices para búsquedas por activo
CREATE INDEX idx_empresa_activo ON empresa(activo);
CREATE INDEX idx_responsable_activo ON responsable(activo);
CREATE INDEX idx_pasajero_activo ON pasajero(activo);
CREATE INDEX idx_viaje_activo ON viaje(activo);
CREATE INDEX idx_participa_activo ON participa(activo);

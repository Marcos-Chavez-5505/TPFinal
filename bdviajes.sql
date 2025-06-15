CREATE DATABASE bdviajes;
USE bdviajes;

-- Tabla empresa
CREATE TABLE empresa (
    id_empresa BIGINT AUTO_INCREMENT,
    e_nombre VARCHAR(150),
    e_direccion VARCHAR(150),
    PRIMARY KEY (id_empresa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

-- Tabla responsable
CREATE TABLE responsable (
    r_numeroempleado BIGINT AUTO_INCREMENT,
    r_numerolicencia BIGINT,
    r_nombre VARCHAR(150), 
    r_apellido VARCHAR(150), 
    PRIMARY KEY (r_numeroempleado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

-- Tabla viaje
CREATE TABLE viaje (
    id_viaje BIGINT AUTO_INCREMENT,              -- Codigo de viaje
    v_destino VARCHAR(150),
    v_cantmaxpasajeros INT,
    v_importe FLOAT,                             -- Campo reubicado antes de las claves foraneas
    id_empresa BIGINT,
    r_numeroempleado BIGINT,
    PRIMARY KEY (id_viaje),
    FOREIGN KEY (id_empresa) REFERENCES empresa(id_empresa)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    FOREIGN KEY (r_numeroempleado) REFERENCES responsable(r_numeroempleado)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

-- Tabla pasajero
CREATE TABLE pasajero (
    p_documento VARCHAR(15),
    p_nombre VARCHAR(150), 
    p_apellido VARCHAR(150), 
    p_telefono VARCHAR(20),                                    
    id_viaje BIGINT,
    PRIMARY KEY (p_documento),                           
    FOREIGN KEY (id_viaje) REFERENCES viaje(id_viaje)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

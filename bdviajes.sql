CREATE DATABASE bdviajes;
USE bdviajes;

-- Tabla empresa
CREATE TABLE empresa (
    idempresa BIGINT AUTO_INCREMENT,
    enombre VARCHAR(150),
    edireccion VARCHAR(150),
    PRIMARY KEY (idempresa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

-- Tabla responsable
CREATE TABLE responsable (
    rnumeroempleado BIGINT AUTO_INCREMENT,
    rnumerolicencia BIGINT,
    rnombre VARCHAR(150), 
    rapellido VARCHAR(150), 
    PRIMARY KEY (rnumeroempleado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

-- Tabla viaje
CREATE TABLE viaje (
    idviaje BIGINT AUTO_INCREMENT,              -- Codigo de viaje
    vdestino VARCHAR(150),
    vcantmaxpasajeros INT,
    vimporte FLOAT,                             -- Campo reubicado antes de las claves foraneas
    idempresa BIGINT,
    rnumeroempleado BIGINT,
    PRIMARY KEY (idviaje),
    FOREIGN KEY (idempresa) REFERENCES empresa(idempresa)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    FOREIGN KEY (rnumeroempleado) REFERENCES responsable(rnumeroempleado)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

-- Tabla pasajero
CREATE TABLE pasajero (
    pdocumento VARCHAR(15),
    pnombre VARCHAR(150), 
    papellido VARCHAR(150), 
    ptelefono VARCHAR(20),                                    
    idviaje BIGINT,
    PRIMARY KEY (pdocumento),                           
    FOREIGN KEY (idviaje) REFERENCES viaje(idviaje)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

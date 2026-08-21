-- =============================================================
-- Esquema de base de datos para el Centralizador de Calificaciones
-- Instituto Paccioli
-- Motor: MySQL 5.7+ / MariaDB 10.3+
-- =============================================================

CREATE DATABASE IF NOT EXISTS centralizador_paccioli
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE centralizador_paccioli;

-- -------------------------------------------------------------
-- Tabla: docentes
-- Acceso al panel de administración
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS docentes (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    password     VARCHAR(255) NOT NULL,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO docentes (password) VALUES
    ('$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); -- paccioli2026

-- -------------------------------------------------------------
-- Tabla: carreras
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS carreras (
    id      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre  VARCHAR(100) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_carreras_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO carreras (nombre) VALUES
    ('Contaduría General'),
    ('Secretariado Ejecutivo'),
    ('Sistemas Informáticos'),
    ('Electrónica'),
    ('Electricidad Industrial'),
    ('Gastronomía');

-- -------------------------------------------------------------
-- Tabla: estudiantes
-- Las cuentas son creadas únicamente por el docente.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS estudiantes (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre       VARCHAR(150) NOT NULL,
    password     VARCHAR(255) NOT NULL,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_estudiantes_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Tabla: notas
-- Las materias se almacenan como texto libre.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notas (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    estudiante_id INT UNSIGNED NOT NULL,
    carrera_id   INT UNSIGNED NOT NULL,
    materia      VARCHAR(120) NOT NULL,
    n1           TINYINT UNSIGNED NOT NULL DEFAULT 0,
    n2           TINYINT UNSIGNED NOT NULL DEFAULT 0,
    n3           TINYINT UNSIGNED NOT NULL DEFAULT 0,
    n4           TINYINT UNSIGNED NOT NULL DEFAULT 0,
    semestre     TINYINT UNSIGNED NOT NULL DEFAULT 1,
    promedio     DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    estado       VARCHAR(10) NOT NULL DEFAULT 'REPROBADO',
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_notas_estudiante FOREIGN KEY (estudiante_id)
        REFERENCES estudiantes (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_notas_carrera FOREIGN KEY (carrera_id)
        REFERENCES carreras (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    KEY idx_notas_semestre (semestre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
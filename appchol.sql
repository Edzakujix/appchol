-- ====================================================================
-- SCRIPT SQL COMPLETO: CREACIÓN DE TABLAS Y REGISTRO DE ACTIVIDADES
-- BASE DE DATOS: Aprendiendo Ch'ol
-- ====================================================================

-- 1. CREAR TABLA: Users (Perfil de Usuarios)
-- Almacena información de perfil y el progreso general.
CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY, 
    google_id VARCHAR(255) UNIQUE NOT NULL, 
    alias VARCHAR(50) NOT NULL,
    age_range VARCHAR(20),
    avatar_id VARCHAR(50),
    current_level INT NOT NULL DEFAULT 1,
    total_score INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------------------

-- 2. CREAR TABLA: Activities (Catálogo de la estructura del curso)
-- Almacena una lista fija de todas las actividades con valor de puntos.
CREATE TABLE Activities (
    activity_id INT AUTO_INCREMENT PRIMARY KEY,
    module_name VARCHAR(50) NOT NULL,
    activity_name VARCHAR(100) NOT NULL,
    points_value INT NOT NULL
);

-- --------------------------------------------------------------------

-- 3. CREAR TABLA: Progress (Registro de intentos de usuario)
-- Enlaza a un usuario (user_id) con una actividad (activity_id) y registra el resultado.
CREATE TABLE Progress (
    progress_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, 
    activity_id INT NOT NULL,
    score_obtained INT NOT NULL,
    is_completed BOOLEAN NOT NULL,
    completion_date DATETIME NOT NULL,
    
    -- Definiciones de Claves Foráneas
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (activity_id) REFERENCES Activities(activity_id) ON DELETE RESTRICT 
);

-- --------------------------------------------------------------------

-- 4. INSERTAR DATOS INICIALES: Catálogo de Actividades (Activities)
-- Estos registros son necesarios para que la lógica de tu backend funcione.

-- Módulo 1: Saludos (3 Actividades, 10 Puntos c/u)
INSERT INTO Activities (module_name, activity_name, points_value) VALUES
('Módulo 1: Saludos', 'Actividad 1: Escucha y Selecciona', 10),
('Módulo 1: Saludos', 'Actividad 2: Partes del Día', 10),
('Módulo 1: Saludos', 'Actividad 3: Unir Palabras', 10);

-- Módulo 2: Presentación Personal (3 Actividades, 10 Puntos c/u)
INSERT INTO Activities (module_name, activity_name, points_value) VALUES
('Módulo 2: Presentación Personal', 'Actividad 2: Identificar Familia', 10),
('Módulo 2: Presentación Personal', 'Actividad 3: Conteo y Números', 10),
('Módulo 2: Presentación Personal', 'Actividad 4: Completar Frase', 10);

-- Módulo 3: Familia (4 Actividades, 10 Puntos c/u)
INSERT INTO Activities (module_name, activity_name, points_value) VALUES
('Módulo 3: Familia', 'Actividad 1: Escucha y Selecciona (Miembro)', 10),
('Módulo 3: Familia', 'Actividad 2: Lee y Selecciona (Frase)', 10),
('Módulo 3: Familia', 'Actividad 3: Forma la Palabra', 10),
('Módulo 3: Familia', 'Actividad 4: Unir Preguntas y Respuestas', 10);
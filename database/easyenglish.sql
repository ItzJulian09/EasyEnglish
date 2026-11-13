-- EasyEnglish database
-- Importeren in phpMyAdmin via het tabblad "Importeren"
-- Let op: een bestaande database "easyenglish" wordt eerst verwijderd!

SET NAMES utf8mb4;

DROP DATABASE IF EXISTS easyenglish;
CREATE DATABASE easyenglish CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE easyenglish;

CREATE TABLE users (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    full_name      VARCHAR(100) NOT NULL,
    email          VARCHAR(150) NOT NULL UNIQUE,
    password_hash  VARCHAR(255) NOT NULL,
    default_public TINYINT(1) NOT NULL DEFAULT 0,
    is_admin       TINYINT(1) NOT NULL DEFAULT 0
);

CREATE TABLE word_lists (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT NOT NULL,
    title        VARCHAR(150) NOT NULL,
    description  TEXT NULL,
    category     VARCHAR(50) NULL,
    direction    VARCHAR(5) NOT NULL DEFAULT 'nl-en',
    is_public    TINYINT(1) NOT NULL DEFAULT 0,
    is_published TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE words (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    list_id  INT NOT NULL,
    dutch    VARCHAR(255) NOT NULL,
    english  VARCHAR(255) NOT NULL,
    position INT NOT NULL DEFAULT 0,
    FOREIGN KEY (list_id) REFERENCES word_lists(id) ON DELETE CASCADE
);

CREATE TABLE attempts (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    list_id       INT NOT NULL,
    correct_count INT NOT NULL,
    total_count   INT NOT NULL,
    score         INT NOT NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (list_id) REFERENCES word_lists(id) ON DELETE CASCADE
);

-- Testgebruikers, het wachtwoord is steeds: welkom123
-- admin01 is beheerder (is_admin = 1)
INSERT INTO users (id, full_name, email, password_hash, default_public, is_admin) VALUES
(1, 'Sven de Vries', 'demo@easyenglish.nl',      '$2y$10$RHb.R/M4LPE1kPmo55sFKe4Qjao.GVqhB19gcjTt4UxfvIiTHZs9a', 0, 0),
(2, 'Henderson',     'henderson@easyenglish.nl', '$2y$10$RHb.R/M4LPE1kPmo55sFKe4Qjao.GVqhB19gcjTt4UxfvIiTHZs9a', 1, 0),
(3, 'TechNL',        'technl@easyenglish.nl',    '$2y$10$RHb.R/M4LPE1kPmo55sFKe4Qjao.GVqhB19gcjTt4UxfvIiTHZs9a', 1, 0),
(4, 'admin01',       'admin01@easyenglish.nl',   '$2y$10$RHb.R/M4LPE1kPmo55sFKe4Qjao.GVqhB19gcjTt4UxfvIiTHZs9a', 0, 1);

INSERT INTO word_lists (id, user_id, title, description, category, direction, is_public, is_published) VALUES
(1, 1, 'Beroepen & Werk', 'Woorden over beroepen en werk.', 'Alledaags', 'nl-en', 0, 1),
(2, 1, 'In de Keuken', 'Alles wat je in de keuken tegenkomt.', 'Alledaags', 'nl-en', 1, 1),
(3, 2, 'Academic Verbs', 'Werkwoorden voor verslagen en essays.', 'Werkwoorden', 'nl-en', 1, 1),
(4, 3, 'Essential IT Terms', 'Engelse woorden voor ICT-studenten.', 'Technisch', 'nl-en', 1, 1);

INSERT INTO words (list_id, dutch, english, position) VALUES
(1, 'Werkgever', 'Employer', 1),
(1, 'Werknemer', 'Employee', 2),
(1, 'Salaris', 'Salary', 3),
(1, 'Vergadering', 'Meeting', 4),
(1, 'Collega', 'Colleague', 5),
(2, 'Koelkast', 'Fridge', 1),
(2, 'Mes', 'Knife', 2),
(2, 'Lepel', 'Spoon', 3),
(2, 'Vork', 'Fork', 4),
(3, 'Analyseren', 'Analyze', 1),
(3, 'Concluderen', 'Conclude', 2),
(3, 'Evalueren', 'Evaluate', 3),
(3, 'Beïnvloeden', 'Influence', 4),
(4, 'Wachtwoord', 'Password', 1),
(4, 'Netwerk', 'Network', 2),
(4, 'Opslaan', 'Save', 3),
(4, 'Bijlage', 'Attachment', 4);

INSERT INTO attempts (user_id, list_id, correct_count, total_count, score, created_at) VALUES
(1, 1, 4, 5, 80, NOW() - INTERVAL 1 DAY),
(1, 2, 2, 4, 50, NOW() - INTERVAL 3 DAY);

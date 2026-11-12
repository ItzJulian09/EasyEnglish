-- =====================================================
-- EasyEnglish - database
-- Importeer dit bestand in phpMyAdmin (tabblad "Importeren")
-- of via de terminal: mysql -u root < database/easyenglish.sql
-- =====================================================

SET NAMES utf8mb4;

DROP DATABASE IF EXISTS easyenglish;
CREATE DATABASE easyenglish CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE easyenglish;

-- -----------------------------------------------------
-- Tabel: users (gebruikers)
-- -----------------------------------------------------
CREATE TABLE users (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    full_name         VARCHAR(100) NOT NULL,
    email             VARCHAR(150) NOT NULL UNIQUE,
    password_hash     VARCHAR(255) NOT NULL,
    site_language     ENUM('nl', 'en') NOT NULL DEFAULT 'nl',
    default_direction ENUM('nl-en', 'en-nl') NOT NULL DEFAULT 'nl-en',
    default_public    TINYINT(1) NOT NULL DEFAULT 0,
    is_admin          TINYINT(1) NOT NULL DEFAULT 0,
    created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- -----------------------------------------------------
-- Tabel: word_lists (woordenlijsten / toetsen)
-- -----------------------------------------------------
CREATE TABLE word_lists (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT NOT NULL,
    title        VARCHAR(150) NOT NULL,
    description  TEXT NULL,
    category     VARCHAR(50) NULL,
    direction    ENUM('nl-en', 'en-nl') NOT NULL DEFAULT 'nl-en',
    is_public    TINYINT(1) NOT NULL DEFAULT 0,
    is_published TINYINT(1) NOT NULL DEFAULT 0,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- -----------------------------------------------------
-- Tabel: words (woordparen in een lijst)
-- -----------------------------------------------------
CREATE TABLE words (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    list_id  INT NOT NULL,
    dutch    VARCHAR(255) NOT NULL,
    english  VARCHAR(255) NOT NULL,
    position INT NOT NULL DEFAULT 0,
    FOREIGN KEY (list_id) REFERENCES word_lists(id) ON DELETE CASCADE
);

-- -----------------------------------------------------
-- Tabel: attempts (gemaakte typ-toetsen)
-- -----------------------------------------------------
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

-- -----------------------------------------------------
-- Tabel: likes (stemmen op openbare lijsten)
-- -----------------------------------------------------
CREATE TABLE likes (
    user_id INT NOT NULL,
    list_id INT NOT NULL,
    PRIMARY KEY (user_id, list_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (list_id) REFERENCES word_lists(id) ON DELETE CASCADE
);

-- =====================================================
-- Testdata
-- Alle testaccounts hebben het wachtwoord: welkom123
-- =====================================================

INSERT INTO users (id, full_name, email, password_hash, default_public) VALUES
(1, 'Sven de Vries',       'demo@easyenglish.nl',      '$2y$10$RHb.R/M4LPE1kPmo55sFKe4Qjao.GVqhB19gcjTt4UxfvIiTHZs9a', 1),
(2, 'Prof. Dr. Henderson', 'henderson@easyenglish.nl', '$2y$10$RHb.R/M4LPE1kPmo55sFKe4Qjao.GVqhB19gcjTt4UxfvIiTHZs9a', 1),
(3, 'LingoExplorer',       'lingo@easyenglish.nl',     '$2y$10$RHb.R/M4LPE1kPmo55sFKe4Qjao.GVqhB19gcjTt4UxfvIiTHZs9a', 1),
(4, 'Emily Watson',        'emily@easyenglish.nl',     '$2y$10$RHb.R/M4LPE1kPmo55sFKe4Qjao.GVqhB19gcjTt4UxfvIiTHZs9a', 1),
(5, 'TechNL',              'technl@easyenglish.nl',    '$2y$10$RHb.R/M4LPE1kPmo55sFKe4Qjao.GVqhB19gcjTt4UxfvIiTHZs9a', 1),
(6, 'admin01',             'admin01@easyenglish.nl',   '$2y$10$RHb.R/M4LPE1kPmo55sFKe4Qjao.GVqhB19gcjTt4UxfvIiTHZs9a', 0);

-- admin01 is beheerder: mag alle toetsen verwijderen
UPDATE users SET is_admin = 1 WHERE users.id = 6;

-- Lijsten van de demo-gebruiker (Sven)
INSERT INTO word_lists (id, user_id, title, description, category, direction, is_public, is_published) VALUES
(1, 1, 'Beroepen & Werk',         'Woorden over beroepen en de werkvloer.',          'Alledaags', 'nl-en', 0, 1),
(2, 1, 'Klimaat & Natuur',        'Woorden over het weer, klimaat en de natuur.',    'Natuur', 'nl-en', 0, 1),
(3, 1, 'In de Keuken',            'Alles wat je in de keuken tegenkomt.',            'Alledaags', 'nl-en', 0, 1),
(4, 1, 'Academische Kernwoorden', 'Moeilijkere woorden voor school en studie.',      'Academisch', 'nl-en', 1, 1);

-- Openbare lijsten van andere gebruikers
INSERT INTO word_lists (id, user_id, title, description, category, direction, is_public, is_published) VALUES
(5, 2, 'Advanced Academic Verbs',  'Een korte selectie van academische kernwerkwoorden voor wetenschappelijke artikelen, essays en formele rapporten op universiteitsniveau.', 'Werkwoorden', 'nl-en', 1, 1),
(6, 3, 'Travel & Airport Basics',  'Handige woorden voor op het vliegveld en op reis.', 'Alledaags', 'nl-en', 1, 1),
(7, 4, 'Business Meetings Idioms', 'Uitdrukkingen die je hoort tijdens zakelijke vergaderingen.', 'Idioom', 'nl-en', 1, 1),
(8, 5, 'Essential IT Terms',       'De belangrijkste Engelse vaktermen voor ICT-studenten.', 'Technisch', 'nl-en', 1, 1);

INSERT INTO words (list_id, dutch, english, position) VALUES
-- 1. Beroepen & Werk
(1, 'Werkgever', 'Employer', 1),
(1, 'Werknemer', 'Employee', 2),
(1, 'Salaris', 'Salary', 3),
(1, 'Sollicitatie', 'Job application', 4),
(1, 'Vergadering', 'Meeting', 5),
(1, 'Collega', 'Colleague', 6),
(1, 'Kantoor', 'Office', 7),
(1, 'Verpleegkundige', 'Nurse', 8),
(1, 'Loodgieter', 'Plumber', 9),
(1, 'Advocaat', 'Lawyer', 10),
-- 2. Klimaat & Natuur
(2, 'Klimaat', 'Climate', 1),
(2, 'Overstroming', 'Flood', 2),
(2, 'Droogte', 'Drought', 3),
(2, 'Bos', 'Forest', 4),
(2, 'Duurzaam', 'Sustainable', 5),
(2, 'Vervuiling', 'Pollution', 6),
(2, 'Aardbeving', 'Earthquake', 7),
(2, 'Onweer', 'Thunderstorm', 8),
-- 3. In de Keuken
(3, 'Koelkast', 'Fridge', 1),
(3, 'Pan', 'Pan', 2),
(3, 'Mes', 'Knife', 3),
(3, 'Lepel', 'Spoon', 4),
(3, 'Vork', 'Fork', 5),
(3, 'Oven', 'Oven', 6),
(3, 'Snijplank', 'Cutting board', 7),
(3, 'Gootsteen', 'Sink', 8),
-- 4. Academische Kernwoorden
(4, 'Nieuwsgierigheid', 'Curiosity', 1),
(4, 'Bereiken', 'Accomplish', 2),
(4, 'Onvermijdelijk', 'Inevitable', 3),
(4, 'Beïnvloeden', 'Influence', 4),
-- 5. Advanced Academic Verbs
(5, 'Beïnvloeden', 'To influence / To affect', 1),
(5, 'Analyseren', 'To analyze', 2),
(5, 'Onderbouwen', 'To substantiate / To back up', 3),
(5, 'Hypothetisch', 'Hypothetical', 4),
(5, 'Concluderen', 'To conclude', 5),
(5, 'Veronderstellen', 'To assume / To presuppose', 6),
(5, 'Illustreren', 'To illustrate', 7),
(5, 'Evalueren', 'To evaluate', 8),
(5, 'Karakteriseren', 'To characterize', 9),
(5, 'Tegenspreken', 'To contradict', 10),
(5, 'Interpreteren', 'To interpret', 11),
(5, 'Onderzoeken', 'To investigate', 12),
-- 6. Travel & Airport Basics
(6, 'Instapkaart', 'Boarding pass', 1),
(6, 'Bagage', 'Luggage', 2),
(6, 'Vertraging', 'Delay', 3),
(6, 'Paspoort', 'Passport', 4),
(6, 'Vertrek', 'Departure', 5),
(6, 'Aankomst', 'Arrival', 6),
-- 7. Business Meetings Idioms
(7, 'Laten we beginnen', 'Let''s get the ball rolling', 1),
(7, 'Op dezelfde lijn zitten', 'To be on the same page', 2),
(7, 'Hoofdlijnen', 'The big picture', 3),
(7, 'Even kort overleggen', 'To touch base', 4),
-- 8. Essential IT Terms
(8, 'Wachtwoord', 'Password', 1),
(8, 'Database', 'Database', 2),
(8, 'Netwerk', 'Network', 3),
(8, 'Opslaan', 'Save', 4),
(8, 'Bijlage', 'Attachment', 5),
(8, 'Beveiliging', 'Security', 6);

INSERT INTO attempts (user_id, list_id, correct_count, total_count, score, created_at) VALUES
(1, 6, 5, 6, 83, NOW() - INTERVAL 7 DAY),
(1, 3, 4, 8, 50, NOW() - INTERVAL 5 DAY),
(1, 4, 4, 4, 100, NOW() - INTERVAL 3 DAY),
(1, 2, 6, 8, 75, NOW() - INTERVAL 1 DAY),
(1, 1, 9, 10, 90, NOW() - INTERVAL 2 HOUR);

INSERT INTO likes (user_id, list_id) VALUES
(1, 5), (3, 5), (4, 5), (5, 5),
(1, 6), (2, 6),
(2, 7), (3, 7), (5, 7),
(4, 8);

-- =====================================================
-- admin.sql
-- Voegt een beheerdersrol toe en maakt admin01 beheerder.
--
-- Gebruik: phpMyAdmin -> database "easyenglish" -> tabblad SQL
--          -> plak dit erin -> Starten
-- Je bestaande gebruikers en toetsen blijven gewoon staan.
-- =====================================================

USE easyenglish;

-- 1. Nieuwe kolom: is deze gebruiker een beheerder? (0 = nee, 1 = ja)
ALTER TABLE users ADD COLUMN IF NOT EXISTS is_admin TINYINT(1) NOT NULL DEFAULT 0;

-- 2. Bestaat admin01 al? Dan wordt dat account beheerder.
--    (we zoeken op naam "admin01" of een e-mailadres dat met admin01 begint)
UPDATE users
SET is_admin = 1
WHERE users.full_name = 'admin01'
   OR users.email = 'admin01'
   OR users.email LIKE 'admin01@%';

-- 3. Bestaat admin01 nog niet? Dan maken we het account aan.
--    Inloggen met: admin01@easyenglish.nl / welkom123
INSERT INTO users (full_name, email, password_hash, is_admin)
SELECT 'admin01', 'admin01@easyenglish.nl', '$2y$10$RHb.R/M4LPE1kPmo55sFKe4Qjao.GVqhB19gcjTt4UxfvIiTHZs9a', 1
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM users
    WHERE users.full_name = 'admin01'
       OR users.email = 'admin01'
       OR users.email LIKE 'admin01@%'
);

-- 4. Controle: dit zijn nu de beheerders
SELECT users.id, users.full_name, users.email, users.is_admin
FROM users
WHERE users.is_admin = 1;

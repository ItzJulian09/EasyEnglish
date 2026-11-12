# EasyEnglish

Een e-learning waarmee je Engels leert door woordjes te typen.
Gemaakt met PHP (OOP + PDO) en MySQL.

## Zo start je het project

### 1. Database klaarzetten
1. Open het **XAMPP Control Panel** en klik bij **MySQL** op **Start**
   (Apache is niet nodig, alleen voor phpMyAdmin).
2. Ga naar <http://localhost/phpmyadmin> → tabblad **Importeren**.
3. Kies het bestand `database/easyenglish.sql` en klik op **Importeren**.

> Let op: dit script maakt de database `easyenglish` opnieuw aan.
> Bestaat die al, dan wordt hij leeggemaakt.

Gebruik je een ander wachtwoord voor MySQL? Pas dan `config/config.php` aan.

### 2. PHP met MySQL-driver
Je VS Code-taak gebruikt `C:\Program Files\php-8.5.10\php.exe`.
Daarin moet de MySQL-driver aan staan:

1. Open `C:\Program Files\php-8.5.10\php.ini`
   (bestaat hij niet? Kopieer dan `php.ini-development` en noem hem `php.ini`).
2. Zorg dat deze regels **geen** `;` ervoor hebben:
   ```ini
   extension_dir = "ext"
   extension=pdo_mysql
   extension=mbstring
   ```
3. Start de PHP-server opnieuw.

Vergeet je dit, dan zie je een duidelijke foutmelding op de website.

### 3. Website starten
In VS Code: **Terminal → Run Task… → Start EasyEnglish PHP server**
en open <http://127.0.0.1:8080/login.php>.

### 4. Inloggen
| E-mailadres            | Wachtwoord |
|------------------------|------------|
| demo@easyenglish.nl    | welkom123  |

Je kunt ook zelf een account registreren.

## Pagina's

| Bestand            | Wat doet het?                                                  |
|--------------------|----------------------------------------------------------------|
| `login.php`        | Inloggen (met "Onthoud mij")                                   |
| `register.php`     | Account aanmaken                                               |
| `index.php`        | Mijn lijsten + Openbare Bibliotheek (zoeken, niveau, sorteren) |
| `toetsbouwer.php`  | Woordenlijst maken of bewerken (`?id=4`)                       |
| `lijst.php`        | Details van een openbare lijst, liken, opslaan                 |
| `toets.php`        | De typ-toets: woord voor woord, met feedback en eindscore      |
| `voortgang.php`    | Beste score en alle pogingen                                   |
| `instellingen.php` | Naam, e-mail, taal, standaard taalrichting en zichtbaarheid    |
| `taal.php`         | Wisselt tussen Nederlands en Engels (NL/EN-knop in de header)  |
| `logout.php`       | Uitloggen                                                      |

## Mappen

```
config/config.php        Database-instellingen
database/easyenglish.sql Tabellen + testdata
src/                     PHP-klassen (namespace App, PSR-4)
  Database.php             PDO-verbinding (singleton)
  UserRepository.php       tabel users
  WordListRepository.php   tabellen word_lists, words, likes
  AttemptRepository.php    tabel attempts
includes/functions.php   Sessie, autoloader, hulpfuncties, header en footer
includes/lang.php        Vertalingen Nederlands → Engels
assets/css/styles.css    Opmaak (kleuren en maten uit Figma)
assets/js/app.js         Rijen toevoegen, wachtwoord tonen, filters
assets/icons/            Iconen uit Figma
```

## Database

```
users       (id, full_name, email, password_hash, site_language, default_direction, default_public)
word_lists  (id, user_id → users, title, description, category, level, direction, is_public, is_published)
words       (id, list_id → word_lists, dutch, english, position)
attempts    (id, user_id → users, list_id → word_lists, correct_count, total_count, score)
likes       (user_id → users, list_id → word_lists)
```

## Beveiliging
- Wachtwoorden met `password_hash()` / `password_verify()`
- Alle queries met prepared statements (geen SQL-injectie)
- Alle uitvoer via `e()` (`htmlspecialchars`, geen XSS)
- Elk formulier heeft een CSRF-token
- Je kunt alleen je eigen lijsten bewerken of verwijderen

De map `storage/` (JSON-bestanden) wordt niet meer gebruikt; alles staat nu in MySQL.

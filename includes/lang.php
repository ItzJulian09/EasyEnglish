<?php
// =====================================================
// lang.php - Nederlands / Engels
//
// In de code schrijf je gewoon Nederlands:   t('Mijn lijsten')
// Staat de website op Engels? Dan zoekt t() de vertaling hieronder op.
// Staat een tekst niet in de lijst, dan blijft hij Nederlands.
//
// Met %d (getal) en %s (tekst) kun je waarden invullen:
//   t('%d woorden', 12)  geeft  "12 woorden"  of  "12 words"
// =====================================================

const TRANSLATIONS = [
    // Header en footer
    'Mijn lijsten' => 'My lists',
    'Toets bouwer' => 'Test builder',
    'Voortgang' => 'Progress',
    'Instellingen' => 'Settings',
    'Inloggen' => 'Log in',
    'Registreren' => 'Sign up',
    'Uitloggen' => 'Log out',
    'Openbare bibliotheek' => 'Public library',
    'Zoek lijsten' => 'Search lists',
    'Niveaus' => 'Levels',
    'Populair' => 'Popular',
    'Hulp & feedback' => 'Help & feedback',
    'Voor docenten' => 'For teachers',
    'Voorwaarden' => 'Terms',
    'Alle rechten voorbehouden.' => 'All rights reserved.',
    'Oefen woordenlijsten, bouw toetsen en volg je voortgang in een rustige, overzichtelijke leeromgeving.'
        => 'Practise word lists, build tests and track your progress in a calm, clear learning environment.',

    // Inloggen en registreren
    'Inloggen bij EasyEnglish' => 'Log in to EasyEnglish',
    'Leer Engels in je eigen tempo' => 'Learn English at your own pace',
    'Account aanmaken' => 'Create account',
    'Account Aanmaken' => 'Create Account',
    'Begin direct met het bouwen van jouw Engelse woordenschat' => 'Start building your English vocabulary right away',
    'Volledige naam' => 'Full name',
    'Volledige Naam' => 'Full Name',
    'E-mailadres' => 'Email address',
    'Wachtwoord' => 'Password',
    'Wachtwoord tonen' => 'Show password',
    'Minimaal 8 tekens' => 'At least 8 characters',
    'bijv. Sven de Vries' => 'e.g. Sven de Vries',
    'bijv. sven@voorbeeld.nl' => 'e.g. sven@example.com',
    'Onthoud mij' => 'Remember me',
    'Nog geen account?' => "Don't have an account?",
    'Heb je al een account?' => 'Already have an account?',
    'Ik ga akkoord met de <b>Algemene Voorwaarden</b> en het <b>Privacybeleid</b> van EasyEnglish.'
        => 'I agree to the <b>Terms and Conditions</b> and the <b>Privacy Policy</b> of EasyEnglish.',
    'Vul je volledige naam in.' => 'Please enter your full name.',
    'Vul een geldig e-mailadres in.' => 'Please enter a valid email address.',
    'Je wachtwoord moet minimaal 8 tekens bevatten.' => 'Your password must be at least 8 characters.',
    'Je moet akkoord gaan met de voorwaarden.' => 'You have to accept the terms.',
    'Dit e-mailadres is al in gebruik.' => 'This email address is already in use.',
    'E-mailadres of wachtwoord is niet juist.' => 'Email address or password is incorrect.',
    'Welkom bij EasyEnglish! Maak je eerste woordenlijst aan.' => 'Welcome to EasyEnglish! Create your first word list.',

    // Mijn lijsten en bibliotheek
    'Oefen je opgeslagen woordenlijsten of maak een nieuwe aan om te starten.'
        => 'Practise your saved word lists or create a new one to get started.',
    'Nieuwe Lijst Toevoegen' => 'Add New List',
    'Je hebt nog geen woordenlijsten. Maak er een aan of sla een lijst op uit de bibliotheek.'
        => "You don't have any word lists yet. Create one or save a list from the library.",
    '%d Engelse woorden' => '%d English words',
    '%d woorden' => '%d words',
    'Bewerken' => 'Edit',
    'Verwijderen' => 'Delete',
    'Weet je zeker dat je deze lijst wilt verwijderen?' => 'Are you sure you want to delete this list?',
    'De woordenlijst is verwijderd.' => 'The word list has been deleted.',
    'Concept' => 'Draft',
    'Score' => 'Score',
    'Nog geen score' => 'No score yet',
    'Laatst geoefend' => 'Last practised',
    'Start Typ Toets' => 'Start Typing Test',
    'Woorden toevoegen' => 'Add words',
    'Openbare Bibliotheek' => 'Public Library',
    'Ontdek duizenden gratis lijsten gedeeld door taaldocenten en de EasyEnglish-community.'
        => 'Discover thousands of free lists shared by language teachers and the EasyEnglish community.',
    'Zoek op onderwerp, niveau of auteur...' => 'Search by topic, level or author...',
    'Niveau' => 'Level',
    'Alle' => 'All',
    'Sorteer' => 'Sort',
    'Meest Populair' => 'Most Popular',
    'Nieuwste' => 'Newest',
    'Naam (A-Z)' => 'Name (A-Z)',
    'Door' => 'By',
    'Geen lijsten gevonden. Probeer een andere zoekterm.' => 'No lists found. Try a different search term.',

    // Datums
    'Nieuw' => 'New',
    'Vandaag' => 'Today',
    'Gisteren' => 'Yesterday',
    '%d dagen geleden' => '%d days ago',
    '1 week geleden' => '1 week ago',
    '%d weken geleden' => '%d weeks ago',

    // Toets bouwer
    'Toetsgegevens' => 'Test details',
    'Titel van de Toets' => 'Test Title',
    'bijv. Academische Kernwoorden' => 'e.g. Academic Key Words',
    'Categorie' => 'Category',
    'bijv. Alledaags' => 'e.g. Everyday',
    'Omschrijving' => 'Description',
    'Waar gaat deze lijst over?' => 'What is this list about?',
    'Taalrichting' => 'Language direction',
    'Zichtbaarheid' => 'Visibility',
    'Openbaar maken' => 'Make public',
    'Indien openbaar, kunnen anderen je lijst zoeken en oefenen.' => 'If public, others can find and practise your list.',
    'Woordenlijst' => 'Word list',
    'woorden' => 'words',
    'Rij Toevoegen' => 'Add Row',
    'Nederlands Woord / Zin' => 'Dutch Word / Sentence',
    'Engelse Vertaling' => 'English Translation',
    'Nieuw Nederlands woord...' => 'New Dutch word...',
    'Engelse vertaling...' => 'English translation...',
    'Concept Opslaan' => 'Save Draft',
    'Publiceren' => 'Publish',
    'Concept opgeslagen.' => 'Draft saved.',
    'Je woordenlijst is gepubliceerd!' => 'Your word list has been published!',
    'Geef je toets een titel.' => 'Give your test a title.',
    'Voeg minimaal één woord toe om te publiceren.' => 'Add at least one word to publish.',
    'Vul bij elke rij zowel het Nederlandse woord als de Engelse vertaling in.'
        => 'Fill in both the Dutch word and the English translation on every row.',
    'Deze lijst bestaat niet of is niet van jou.' => "This list doesn't exist or isn't yours.",

    // Lijst details
    'Terug naar Bibliotheek' => 'Back to Library',
    'Gemaakt door' => 'Created by',
    'Woorden' => 'Words',
    'Stemmen' => 'Votes',
    'Stem geven' => 'Vote',
    'Stem weghalen' => 'Remove vote',
    'Start Toets (Typen)' => 'Start Test (Typing)',
    'Opslaan in mijn Bibliotheek' => 'Save to my Library',
    'Voorbeeldwoorden' => 'Sample words',
    'van' => 'of',
    'Nederlands Woord / Uitdrukking' => 'Dutch Word / Expression',
    '"%s" staat nu in jouw lijsten.' => '"%s" is now in your lists.',
    'Deze lijst bestaat niet of is niet openbaar.' => "This list doesn't exist or isn't public.",

    // Typ-toets
    'Typ Toets' => 'Typing Test',
    'Deze lijst heeft nog geen woorden.' => "This list doesn't have any words yet.",
    'Vertaal naar het Engels' => 'Translate into English',
    'Vertaal naar het Nederlands' => 'Translate into Dutch',
    'Woord %d van %d' => 'Word %d of %d',
    'Typ hier je antwoord...' => 'Type your answer here...',
    'Overslaan' => 'Skip',
    'Controleer Antwoord' => 'Check Answer',
    'Goed zo!' => 'Well done!',
    'Oeps! Net niet helemaal goed.' => 'Oops! Not quite right.',
    'Jouw antwoord' => 'Your answer',
    'Juiste spelling is' => 'Correct spelling is',
    'Overgeslagen' => 'Skipped',
    'overgeslagen' => 'skipped',
    'Het juiste antwoord is' => 'The correct answer is',
    'Volgende' => 'Next',
    'Bekijk resultaat' => 'See result',
    'Toets afgerond' => 'Test completed',
    'Je had %d van de %d woorden goed.' => 'You got %d out of %d words right.',
    'Woord' => 'Word',
    'Juiste antwoord' => 'Correct answer',
    'Alles goed, top gedaan!' => 'All correct, great job!',
    'Naar mijn lijsten' => 'Go to my lists',
    'Opnieuw oefenen' => 'Practise again',

    // Voortgang
    'Mijn Prestaties & Geschiedenis' => 'My Results & History',
    'Bekijk je beste score en elke poging in één rustig overzicht.' => 'See your best score and every attempt in one calm overview.',
    'Beste Score' => 'Best Score',
    'op' => 'on',
    'Maak je eerste toets om hier je beste score te zien.' => 'Take your first test to see your best score here.',
    'Zoek op toetsnaam...' => 'Search by test name...',
    'Datum & Tijd' => 'Date & Time',
    'Naam Woordenlijst' => 'Word List Name',
    'Geen pogingen gevonden voor deze zoekterm.' => 'No attempts found for this search term.',
    'Je hebt nog geen toetsen gemaakt.' => "You haven't taken any tests yet.",

    // Instellingen
    'Beheer je accountgegevens, taal en privacyvoorkeuren.' => 'Manage your account details, language and privacy preferences.',
    'Accountgegevens' => 'Account details',
    'Voorkeuren' => 'Preferences',
    'Taal van de Website' => 'Website Language',
    "Dit verandert de taal van knoppen, menu's en uitleg." => 'This changes the language of buttons, menus and explanations.',
    'Standaard Taalrichting' => 'Default Language Direction',
    'Wordt gebruikt bij elke nieuwe toets die je maakt.' => 'Used for every new test you create.',
    'Zichtbaarheid Nieuwe Toetsen' => 'Visibility of New Tests',
    'Nieuwe woordenlijsten standaard openbaar maken' => 'Make new word lists public by default',
    'Instellingen Opslaan' => 'Save Settings',
    'Je instellingen zijn opgeslagen.' => 'Your settings have been saved.',
];

/**
 * De huidige taal: eerst die van de ingelogde gebruiker, anders uit de sessie, anders Nederlands.
 */
function currentLanguage(): string
{
    if (isset($_SESSION['language']) && in_array($_SESSION['language'], ['nl', 'en'], true)) {
        return $_SESSION['language'];
    }

    return 'nl';
}

/**
 * Vertaalt een Nederlandse tekst naar de huidige taal.
 */
function t(string $text, mixed ...$values): string
{
    if (currentLanguage() === 'en' && isset(TRANSLATIONS[$text])) {
        $text = TRANSLATIONS[$text];
    }

    return $values ? sprintf($text, ...$values) : $text;
}

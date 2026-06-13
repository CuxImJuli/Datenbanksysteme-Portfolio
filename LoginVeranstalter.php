<?php
/* 
Autor: Linda Maaß
Anmeldung eines existierenden Rennveranstalters
*/

require_once __DIR__ . '/session-init.php';
require_once __DIR__ . '/process.php';

$dbConnection = connectToDatabase();

// Prüft Zugangsdaten und meldet den Veranstalter an
function loginOrganizer(PDO $dbConnection, string $name, string $password): bool {
    $existingOrganizer = findOrganizer($dbConnection, $name);

    if (!$existingOrganizer) {
        return false;
    }

    // Passwort prüfen und Session starten
    if (password_verify($password, $existingOrganizer['Passwort'])) {
        session_regenerate_id(true);
        $_SESSION['organizer_name'] = $existingOrganizer['Name'];
        $_SESSION['role'] = 'organizer';
        header('Location: RennenAnlegen.php');
        exit();
    }
    return false;
}

// Formulardaten verarbeiten
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $inputName = trim($_POST['organizer_name'] ?? '');
    $inputPassword = $_POST['password'] ?? '';

    if ($inputName && $inputPassword) {
        try {
                if (!loginOrganizer($dbConnection, $inputName, $inputPassword)) {
                    echo "Fehler: Name oder Passwort falsch.";
}
            
        } catch (PDOException $error) {
            echo "Datenbankfehler";
        }
    } else {
        echo "Bitte alle Felder ausfüllen.";
    }
} else {
    echo "Bitte nutzen Sie das Formular auf der Anmeldeseite.";
}
?>
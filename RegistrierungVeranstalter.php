<?php

/* 
Autor: Linda Maaß
Registrierung eines neuen Rennveranstalters
*/

require_once __DIR__ . '/session-init.php';
require_once __DIR__ . '/process.php';


$dbConnection = connectToDatabase();

// Legt einen neuen Rennveranstalter an und hasht das Passwort
function registerOrganizer(PDO $dbConnection, string $name, string $password): bool {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $query = "INSERT INTO Rennveranstalter (Name, Passwort) VALUES (:name, :password)";
    $statement = $dbConnection->prepare($query);
    return $statement->execute([
        ':name' => $name,
        ':password' => $hashedPassword
    ]);
}


// Nur POST-Anfragen verarbeiten
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo "Bitte nutzen Sie das Formular auf der Anmeldeseite.";
    exit;
}

$inputName = trim($_POST['organizer_name'] ?? '');
$inputPassword = $_POST['password'] ?? '';

if ($inputName === '' || $inputPassword === '') {
    echo "Bitte alle Felder ausfuellen.";
    exit;
}

try {
    if (findOrganizer($dbConnection, $inputName)) {
        echo "Fehler: Name bereits vergeben.";
        exit;
    }

    if (registerOrganizer($dbConnection, $inputName, $inputPassword)) {
        session_regenerate_id(true);
        $_SESSION['organizer_name'] = $inputName;
        $_SESSION['role'] = 'organizer';
        header('Location: RennenAnlegen.php');
        exit;
    } else {
        echo "Fehler: Registrierung fehlgeschlagen.";
    }
} catch (PDOException $error) {
    echo "Datenbankfehler";
}
?>
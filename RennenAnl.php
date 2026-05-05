<?php

ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

require_once __DIR__ . '/process.php';

$dbConnection = connectToDatabase();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organizer') {
    header('Location: LoginVeran.php');
    exit;
}

function createNewEvent(PDO $dbConnection, string $name, string $date, string $startLocation, float $distanceKM, int $elevationM, float $maxGradient): bool {
    $statement = $dbConnection->prepare("INSERT INTO Rennen (Name, Datum, Startort, AnzahlKM, Höhenmeter, MaxSteigung)
        VALUES (:name, :date, :startLocation, :distanceKM, :elevationM, :maxGradient)");

    return $statement->execute([
        ':name' => $name,
        ':date' => $date,
        ':startLocation' => $startLocation,
        ':distanceKM' => $distanceKM,
        ':elevationM' => $elevationM,
        ':maxGradient' => $maxGradient
    ]);
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo "Bitte nutzen Sie das Formular zum Anlegen eines Rennens.";
    exit;
}

$date = trim($_POST['date'] ?? '');
$startLocation = trim($_POST['start_location'] ?? '');
$distanceKM = $_POST['distance_km'] ?? '';
$elevationM = $_POST['elevation_m'] ?? '';
$maxGradient = $_POST['max_gradient'] ?? '';

if ($date === '' || $startLocation === '' || $distanceKM === '' || $elevationM === '' || $maxGradient === '') {
    echo "Fehler: Bitte alle Felder ausfüllen.";
    exit;
}

try {
$success = createNewEvent($dbConnection, $_SESSION['organizer_name'], $date, $startLocation, (float)$distanceKM, (int)$elevationM, (float)$maxGradient);
    echo $success
        ? "Rennen erfolgreich angelegt! <a href='RennenAnl.html'>Zurück zur Übersicht</a>"
        : "Fehler: Rennen konnte nicht angelegt werden.";
} catch (PDOException $error) {
    echo "Datenbankfehler";
}

?>
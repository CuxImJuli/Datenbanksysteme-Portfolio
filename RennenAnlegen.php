<?php
/* 
Autor: Linda Maaß
Ein neues Rennen anlegen (durch den Rennveranstalter)
*/

require_once __DIR__ . '/session-init.php';
require_once __DIR__ . '/process.php';

$dbConnection = connectToDatabase();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organizer') {
    header('Location: index.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    require __DIR__ . '/RennenAnlegen.html';
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo "Bitte nutzen Sie das Formular zum Anlegen eines Rennens.";
    exit;
}

// Eingaben einlesen und bereinigen
$date = trim($_POST['date'] ?? '');
$startLocation = trim($_POST['start_location'] ?? '');
$distanceKM = $_POST['distance_km'] ?? '';
$elevationM = $_POST['elevation_m'] ?? '';
$maxGradient = $_POST['max_gradient'] ?? '';
$organizerName = trim($_SESSION['organizer_name'] ?? '');


if ($date === '' || $startLocation === '' || $distanceKM === '' || $elevationM === '' || $maxGradient === '') {
    echo "Fehler: Bitte alle Felder ausfuellen. <a href='RennenAnlegen.php'>Zurueck</a>";
    exit;
}

if ($organizerName === '') {
    echo "Fehler: Kein Veranstaltername in der Session gefunden. <a href='index.php'>Zurueck</a>";
    exit;
}

try {
    // Stored Procedure aufrufen
    $callStatement = $dbConnection->prepare("CALL ErstelleRennen(
        :date,
        :startLocation,
        :distanceKM,
        :elevationM,
        :maxGradient,
        :organizerName,
        @statusCode,
        @newID)");

    $callStatement->execute([
        ':date' => $date,
        ':startLocation' => $startLocation,
        ':distanceKM' => (float)$distanceKM,
        ':elevationM' => (int)$elevationM,
        ':maxGradient' => (float)$maxGradient,
        ':organizerName' => $organizerName,
]);
    $callStatement->closeCursor();

    // OUT-Parameter der Stored Procedure auslesen
    $statusQuery = $dbConnection->prepare("SELECT @statusCode AS statusCode, @newID AS newID");
    $statusQuery->execute();
    $result = $statusQuery->fetch(PDO::FETCH_ASSOC);
    $statusCode = (int)$result['statusCode'];
    $newID = $result['newID'];

    // Fehlermeldungen definieren
    $errorMessages = [
        1 => "Fehler: Das Datum muss in der Zukunft liegen.",
        2 => "Fehler: Die Kilometeranzahl muss groesser als 0 sein.",
        3 => "Fehler: Die Maximale Steigung muss groesser als 0 sein.",
    ];

    if ($statusCode === 0) {
        header('Location: RennenAnlegen.php?created=1');
        exit;
    } else {
        $message = $errorMessages[$statusCode] ?? "Unbekannter Fehler.";
        echo htmlspecialchars($message) . " <a href='RennenAnlegen.php'>Zurueck</a>";
    }

} catch (PDOException $error) {
    echo "Datenbankfehler";
}
?>
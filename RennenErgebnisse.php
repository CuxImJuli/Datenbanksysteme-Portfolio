<?php

/* 
Autor: Linda Maaß
Verarbeitet die Erfassung von Rennergebnissen durch den Veranstalter 
*/

require_once __DIR__ . '/session-init.php';
require_once __DIR__ . '/process.php';


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organizer') {
    header('Location: index.php');
    exit();
}

$db = connectToDatabase();
$error = '';
$success = '';

// Rennen anhand der ID aus der Datenbank laden
function getRace(PDO $db, int $id): array|false {
    $statement = $db->prepare("SELECT ID, Datum, Startort FROM Rennen WHERE ID = :id");
    $statement->execute([':id' => $id]);
    return $statement->fetch(PDO::FETCH_ASSOC);
}

function isValidRaceTime(string $time): bool {
    return preg_match('/^(\d|[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/', $time) === 1;
}

// Alle Teilnehmer eines Rennens sortiert nach Startnummer laden
function getParticipants(PDO $db, int $id): array {
    $statement = $db->prepare("SELECT * FROM v_RennenTeilnehmer 
                                 WHERE Rennen_ID = :id ORDER BY StartNr");
    $statement->execute([':id' => $id]);
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

// Prüft ob bereits Ergebnisse erfasst wurden: wenn ja ist eine Änderung nicht mehr möglich
function isLocked(PDO $db, int $id): bool {
    $statement = $db->prepare("SELECT
                                   COUNT(*) AS total_rows,
                                   SUM(CASE
                                       WHEN Platzierung IS NOT NULL
                                        AND Platzierung > 0
                                        AND Fahrtzeit IS NOT NULL
                                        AND Fahrtzeit <> '00:00:00'
                                       THEN 1
                                       ELSE 0
                                   END) AS completed_rows
                               FROM Teilnahme
                               WHERE Rennen_ID = :id");
    $statement->execute([':id' => $id]);
    $result = $statement->fetch(PDO::FETCH_ASSOC);

    $totalRows = (int) ($result['total_rows'] ?? 0);
    $completedRows = (int) ($result['completed_rows'] ?? 0);

    // Nur sperren, wenn für alle Teilnehmer vollständige Ergebnisse gespeichert sind
    return $totalRows > 0 && $completedRows === $totalRows;
}

// Ergebnisse verarbeiten und speichern
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raceId = (int) ($_POST['race_id'] ?? 0);

    if ($raceId <= 0) {
        $error = "Ungueltige ID";
    } elseif (!getRace($db, $raceId)) {
        $error = "Rennen nicht gefunden";
    } else {
        try {
            $db->beginTransaction();

            // Sperrt alle Teilnahme-Zeilen des Rennens, um parallele Änderungen zu verhindern.
            $lockStatement = $db->prepare("SELECT Mitarbeiter_ID FROM Teilnahme WHERE Rennen_ID = :id FOR UPDATE");
            $lockStatement->execute([':id' => $raceId]);
            $lockStatement->fetchAll(PDO::FETCH_ASSOC);

            if (isLocked($db, $raceId)) {
                $error = "Ergebnisse schon vorhanden!";
                throw new RuntimeException('Ergebnisse gesperrt');
            }

            $participantForCheck = getParticipants($db, $raceId);

            if ($participantForCheck === []) {
                $error = "Keine Teilnehmer für dieses Rennen gefunden.";
                throw new RuntimeException('Keine Teilnehmer');
            }
            // Eingaben vor der Transaktion prüfen
            foreach ($participantForCheck as $driver) {
                $driverId = (int) $driver['Mitarbeiter_ID'];
                $rank = trim((string) ($_POST['platzierung'][$driverId] ?? ''));
                $time = trim((string) ($_POST['fahrtzeit'][$driverId] ?? ''));

                if ($rank === '' || $time === '') {
                    $error = "Bitte Platzierung und Fahrtzeit eintragen.";
                    throw new RuntimeException('Unvollständige Eingaben');
                }

                if (!isValidRaceTime($time)) {
                    $error = "Fahrtzeit muss im Format hh:mm:ss angegeben werden.";
                    throw new RuntimeException('Ungueltiges Zeitformat');
                }

                // Platzierung darf nicht negativ oder 0 sein
                if ((int) $rank < 1) {
                    $error = "Platzierung muss eine positive Zahl sein (mindestens 1).";
                    throw new RuntimeException('Ungueltige Platzierung');
                }
            }
            // Ergebnisse speichern
            $statement = $db->prepare("UPDATE Teilnahme SET Platzierung = :p, Fahrtzeit = :z 
                                        WHERE Rennen_ID = :rid AND Mitarbeiter_ID = :fid");

            foreach ($participantForCheck as $driver) {
                $driverId = (int) $driver['Mitarbeiter_ID'];
                $rank = trim((string) $_POST['platzierung'][$driverId]);
                $time = trim((string) $_POST['fahrtzeit'][$driverId]);

                $statement->execute([
                    ':p' => (int) $rank,
                    ':z' => $time,
                    ':rid' => $raceId,
                    ':fid' => $driverId,
                ]);
            }

            $db->commit();
            $success = "Ergebnisse wurden gespeichert. Eine Aenderung ist nicht mehr moeglich.";
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            if ($error === '') {
                $error = "DB-Fehler!";
            }
        }
    }
}

// Renn- und Teilnehmerdaten für die Anzeige laden
$raceId = (int) ($_GET['race_id'] ?? $_POST['race_id'] ?? 0);
$race = $raceId ? getRace($db, $raceId) : null;
$participant = $race ? getParticipants($db, $raceId) : [];
$locked = $race ? isLocked($db, $raceId) : false;

require __DIR__ . '/RennenErgebnisseTemplate.php';
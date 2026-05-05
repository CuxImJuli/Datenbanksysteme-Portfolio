<?php
// Author: Noah S. Kipp
// Ansicht und Controller für die Auswertung der Trainingsdaten eines Teams.
session_start();
require_once __DIR__ . '/process.php';
require_once __DIR__ . '/fahrerauswertung.php';

// Überprüfen, ob der Teamchef eingeloggt ist
if (empty($_SESSION['loginname'])) { header("Location: teamlogin.php"); exit; }

try {
    $pdo = connectToDatabase(); // DB-Verbindung herstellen
    
    // Teamnamen des eingeloggten Teamchefs ermitteln
    $stmt = $pdo->prepare("SELECT Teamname FROM Team WHERE Loginname = :loginname LIMIT 1");
    $stmt->execute([':loginname' => $_SESSION['loginname']]);
    if (!($teamname = $stmt->fetchColumn())) die("Kein Team gefunden.");

    // Alle verfügbaren Trainingsziele für das Filter-Dropdown laden
    $ziele = $pdo->query("SELECT Zielname FROM Trainingsziel ORDER BY Zielname")->fetchAll(PDO::FETCH_ASSOC);
    
    // Liste aller Fahrer dieses Teams holen
    $stmt = $pdo->prepare("SELECT Mitarbeiter_ID FROM Fahrer WHERE Teamname = :teamname ORDER BY Mitarbeiter_ID");
    $stmt->execute([':teamname' => $teamname]);
    $fahrerIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    die("Datenbankfehler: " . htmlspecialchars($e->getMessage()));
}

// Filterparameter auslesen (mit Standardwerten)
$filterZiel = $_GET['zielname'] ?? 'Alle Ziele';
$filterStart = $_GET['startdatum'] ?? '';
$filterEnd = $_GET['enddatum'] ?? '';

$auswertung = [];
// Instanziieren der TeamAuswertung und Abrufen der Daten für das gesamte Team (Löst das N+1 Query Problem)
$obj = new TeamAuswertung($pdo, $teamname);
$obj->setZielname($filterZiel);
$obj->setZeitraum($filterStart, $filterEnd);
$obj->ermittleKennzahlen();
$auswertung = $obj->getAuswertung();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Auswertung - Trainings Manager</title>

</head>
<body>
    <h1>Auswertung für Team <?= htmlspecialchars($teamname) ?></h1>
    <p><a href="teamchefmenu.php">[Zurück zum Menü]</a></p>
    <hr>

    <form action="teamchefauswertung.php" method="get">
        <label for="zielname">Trainingsziel:</label><br>
        <select id="zielname" name="zielname">
            <option value="Alle Ziele" <?= $filterZiel === 'Alle Ziele' ? 'selected' : '' ?>>Alle Ziele</option>
            <?php foreach ($ziele as $z): ?>
                <option value="<?= htmlspecialchars($z['Zielname']) ?>" <?= $filterZiel === $z['Zielname'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($z['Zielname']) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>
        
        <label for="startdatum">Startdatum (optional):</label><br>
        <input type="date" id="startdatum" name="startdatum" value="<?= htmlspecialchars($filterStart) ?>"><br><br>

        <label for="enddatum">Enddatum (optional):</label><br>
        <input type="date" id="enddatum" name="enddatum" value="<?= htmlspecialchars($filterEnd) ?>"><br><br>

        <input type="submit" value="Filtern">
    </form>

    <?php if (empty($auswertung)): ?>
        <p>Keine Trainingsdaten für die gewählten Filter gefunden.</p>
    <?php else: ?>
        <table border="1">
            <thead>
                <tr>
                    <th>Mitarbeiter-ID</th>
                    <th>Monat</th>
                    <th>Einträge</th>
                    <th>Summe (km)</th>
                    <th>Durchschnitt (km)</th>
                    <th>Minimum (km)</th>
                    <th>Maximum (km)</th>
                    <th>Median (km)</th>
                    <th>Std.Abw.</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($auswertung as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['Mitarbeiter_ID']) ?></td>
                        <td><?= htmlspecialchars($row['Monat']) ?></td>
                        <td><?= $row['count'] ?></td>
                        <?php foreach (['sum', 'avg', 'min', 'max', 'median', 'stddev'] as $k): ?>
                            <td><?= number_format($row[$k], 2, ',', '.') ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>

<?php
/**
 * Author: Noah S. Kipp
 */
// Starten der Session und Einbinden der notwendigen Funktionen
session_start();
require_once __DIR__ . '/process.php';
require_once __DIR__ . '/trainingsauswertung.php';

// Überprüfen, ob der Teamchef eingeloggt ist
if (empty($_SESSION['loginname'])) {
    header("Location: teamlogin.php");
    exit;
}

try {
    $pdo = connectToDatabase();

    // Teamnamen des Teamchefs ermitteln
    $stmt = $pdo->prepare("SELECT Teamname FROM Team WHERE Loginname = :loginname LIMIT 1");
    $stmt->execute([':loginname' => $_SESSION['loginname']]);
    if (!($teamname = $stmt->fetchColumn()))
        die("Kein Team gefunden.");

    // Alle verfügbaren Trainingsziele laden
    $ziele = $pdo->query("SELECT Zielname FROM Trainingsziel ORDER BY Zielname")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Datenbankfehler: " . htmlspecialchars($e->getMessage()));
}

// Filterparameter auslesen
$filterZiel = $_GET['zielname'] ?? 'Alle Ziele';
$filterStart = $_GET['startdatum'] ?? '';
$filterEnd = $_GET['enddatum'] ?? '';

// Kennzahlen ermitteln
$obj = new TeamAuswertung();
$obj->setTeamname($teamname);
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

    <form method="get">
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
                    <th>Standardabweichung</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($auswertung as $fahrerId => $monate): ?>
                    <?php foreach ($monate as $monat => $k): ?>
                        <tr>
                            <td><?= htmlspecialchars($fahrerId) ?></td>
                            <td><?= htmlspecialchars($monat) ?></td>
                            <td><?= $k['Anzahl'] ?></td>
                            <td><?= number_format($k['Summe'], 2, ',', '.') ?></td>
                            <td><?= number_format($k['Durchschnitt'], 2, ',', '.') ?></td>
                            <td><?= number_format($k['Minimum'], 2, ',', '.') ?></td>
                            <td><?= number_format($k['Maximum'], 2, ',', '.') ?></td>
                            <td><?= number_format($k['Median'], 2, ',', '.') ?></td>
                            <td><?= number_format($k['Standardabweichung'], 2, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>

</html>
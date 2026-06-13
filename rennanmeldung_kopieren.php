<?php
session_start();
require_once __DIR__ . '/process.php';
require_once __DIR__ . '/rennanmeldung.inc.php'; // Bindet alle SQL-Funktionen ein

// Prüft, ob der Nutzer eingeloggt ist. empty() stellt sicher, dass die Session-Variable 
// existiert nicht leer ist. Falls nicht, erfolgt ein sofortiger Redirect zum Login.
if (empty($_SESSION['loginname'])) {
    header("Location: index.php");
    exit;
}
// Übernahme des verifizierten Loginnamens aus der Session in eine lokale Variable
$loginname = $_SESSION['loginname'];
//leere Variable für spätere Erfolgs- bzw. Fehlermeldung
$meldung = "";
//Initialisierung
$quellRennenListe = [];
$zielRennenListe = [];
$fahrerListe = [];
$quell_name = "";

try {
    $pdo = connectToDatabase();
    
    // Teamname aus funktion holen
    $teamname = getTeamnameByLogin($pdo, $loginname); 

    if (!$teamname) {
        die("Fehler: Ihrem Benutzerkonto ist kein Team zugeordnet.");
    }
    // Sicheres Auslesen der POST-Daten mit isset() und Typkonvertierung via intval() 
    // zum Schutz vor Manipulationen
    $action   = isset($_POST['action']) ? $_POST['action'] : '';
    $quell_id = isset($_POST['quell_id']) ? intval($_POST['quell_id']) : 0;
    //prüfen ob Post abgesendet wurde
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'copy') {
        $ziel_id = isset($_POST['ziel_id']) ? intval($_POST['ziel_id']) : 0;
        //Fehlermeldungen
        if ($quell_id === 0 || $ziel_id === 0) {
            $meldung = "Fehler: Quell- und Zielrennen müssen ausgewählt sein.";
        } elseif ($quell_id === $ziel_id) {
            $meldung = "Fehler: Quell- und Zielrennen dürfen nicht identisch sein.";
        } else {
            // Nutzt ausgelagerte Funktion zum Zählen
            $anzahlFahrer = countDriversInRace($pdo, $quell_id, $teamname);

            if ($anzahlFahrer === 0) {
                $meldung = "Fehler: In diesem Quell-Rennen befinden sich keine Fahrer Ihres Teams.";
            } else {
                // Aufruf der Kopierfunktion
                $ergebnis = rennenKopieren($pdo, $quell_id, $ziel_id, $teamname);
                
                if ($ergebnis === true) {
                    //verhindert doppeltes Ausführen bei einem F5-Seitenreload
                    header("Location: rennanmeldung_kopieren.php?success=" . $anzahlFahrer);
                    exit;
                } else {
                    $meldung = $ergebnis;
                }
            }
        }
    }
    // Daten über die ausgelagerten Funktionen laden
    $quellRennenListe = getQuellRennenListe($pdo, $teamname);

    if ($quell_id > 0) {
        $quell_name      = getRennenName($pdo, $quell_id);
        $zielRennenListe = getZielRennenListe($pdo, $quell_id); // Nutzt die View intern
        $fahrerListe     = getFahrerInRennen($pdo, $quell_id, $teamname);
    }

} catch (PDOException $e) {
    $meldung = "Fehler: " . htmlspecialchars($e->getMessage());
}

// Dynamische Erfolgsmeldung auswerten
if (isset($_GET['success'])) {
    $anzahl = intval($_GET['success']);
    $meldung = "Erfolgreich! Es wurden " . $anzahl . " Fahrer Ihres Teams übertragen.";
    $quell_id = 0; 
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Anmeldungen übertragen</title>
</head>
<body>

    <h1>Anmeldungen übertragen</h1>
    <p>Team: <strong><?= htmlspecialchars($teamname) ?></strong></p>
    
    <?php if (!empty($meldung)): ?>
        <p><strong><?= $meldung ?></strong></p>
    <?php endif; ?>
    <!-- Quell Rennen auswählen -->
    <?php if ($quell_id === 0): ?>
        <form method="post" action="rennanmeldung_kopieren.php">
            <input type="hidden" name="action" value="select_source">
            
            <label for="quell_id">Aus welchem Rennen möchten Sie die Fahrer übertragen?</label><br><br>
            <select name="quell_id" id="quell_id" required>
                <option value="">Quell-Rennen auswählen</option>
                <?php foreach ($quellRennenListe as $qr): ?>
                    <option value="<?= $qr['ID'] ?>">
                        <?= htmlspecialchars($qr['Name']) ?> (<?= date('d.m.Y', strtotime($qr['Datum'])) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <br><br>
            <input type="submit" value="Rennen bestätigen und Fahrer anzeigen">
        </form>
    <!-- Zielrennen auswählen und Bestätigen -->
    <?php else: ?>
        <p>Ausgewähltes Rennen: <strong><?= htmlspecialchars($quell_name) ?></strong></p>
        <!--Liste der bisher angemeldeten Fahrer-->
        <h3>Folgende Fahrer Ihres Teams werden übertragen:</h3>
        <table border="1" cellpadding="5" style="margin-bottom: 20px; border-collapse: collapse;">
            <thead>
                <tr>
                    <th>Fahrer-ID</th>
                    <th>Nachname</th>
                    <th>Vorname</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($fahrerListe)): ?>
                    <?php foreach ($fahrerListe as $fahrer): ?>
                        <tr>
                            <td><?= htmlspecialchars($fahrer['Mitarbeiter_ID']) ?></td>
                            <td><?= htmlspecialchars($fahrer['Nachname'] ?? '') ?></td>
                            <td><?= htmlspecialchars($fahrer['Vorname'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">Keine Fahrer Ihres Teams für dieses Rennen gefunden.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <!-- finale Absendeformular -->
        <form method="post" action="rennanmeldung_kopieren.php">
            <input type="hidden" name="action" value="copy">
            <input type="hidden" name="quell_id" value="<?= $quell_id ?>">

            <label for="ziel_id">In welches zukünftige Rennen sollen diese Fahrer übertragen werden?</label><br><br>
            <select name="ziel_id" id="ziel_id" required>
                <option value="">Ziel-Rennen auswählen</option>
                <?php foreach ($zielRennenListe as $zr): ?>
                    <option value="<?= $zr['ID'] ?>">
                        <?= htmlspecialchars($zr['Name']) ?> (<?= date('d.m.Y', strtotime($zr['Datum'])) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <br><br>
            <input type="submit" value="Fahrer jetzt übertragen">
        </form>
        
        <br>
        <p><a href="rennanmeldung_kopieren.php">Abbrechen und anderes Quell-Rennen wählen</a></p>
        
    <?php endif; ?>

    <hr>
    <p><a href="teamchefmenu.php">Zurück zum Hauptmenü</a></p>

</body>
</html>
<?php
//author: Juri Schröder
//Menü für die Anmeldung für Rennen
session_start();
require_once __DIR__ . '/process.php';
require_once __DIR__ . '/rennanmeldung.inc.php'; // Bindet die ausgelagerten SQL-Funktionen ein
// Prüft, ob der Nutzer eingeloggt ist. empty() stellt sicher, dass die Session-Variable 
// existiert nicht leer ist. Falls nicht, erfolgt ein sofortiger Redirect zum Login.
if (empty($_SESSION['loginname'])) {
    header("Location: index.html");
    exit;
}
// Übernahme des verifizierten Loginnamens aus der Session in eine lokale Variable
$loginname = $_SESSION['loginname'];
//leere Variable für spätere Erfolgs- bzw. Fehlermeldung
$meldung = "";
//Initialisierung mit leeren Standardwerten (ID = 0 steht für eine Neuanlage).
$zukuenftigeRennen = [];
$teamFahrer = [];

try {
    $pdo = connectToDatabase();

    // Datengrundlage über die ausgelagerten Funktionen laden
    $teamname          = getTeamnameByLogin($pdo, $loginname);

    if (!$teamname) {
        die("Fehler: Ihrem Benutzerkonto ist kein Team zugeordnet.");
    }
    $zukuenftigeRennen = getZukuenftigeRennen($pdo);
    $teamFahrer        = getTeamFahrer($pdo, $teamname);

    $maxFahrerImTeam = count($teamFahrer);

    // Prüft mit isset(), ob das Formular per POST gesendet wurde und 
    // wertet den unsichtbaren 'action'-Parameter aus.
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_registration') {
        
        // Typkonvertierung zu Integer als Sicherheitsmaßnahme
        $rennen_id = intval($_POST['rennen_id']);
        
        //Falls kein Fahrer gewählt wurde, wird ein leeres Array genutzt
        $gewaehlte_fahrer_ids = $_POST['fahrer_ids'] ?? [];

        // Doppelte Auswahl im Formular prüfen
        // Verhindert, dass der Nutzer in den Dropdowns denselben Fahrer mehrfach auswählt
        if (count($gewaehlte_fahrer_ids) !== count(array_unique($gewaehlte_fahrer_ids))) {
            $meldung = "Fehler: Ein Fahrer darf nicht mehrfach für dasselbe Rennen ausgewählt werden!";
            $_POST['anzahl_fahrer'] = count($gewaehlte_fahrer_ids);
            $_POST['rennen_id'] = $rennen_id;
        } else {
            
            // Entweder werden alle gewählten 
            // Fahrer komplett gespeichert, oder im Fehlerfall wird der gesamte Block verworfen.
            $pdo->beginTransaction();
            $duplikatGefunden = false;
            
            foreach ($gewaehlte_fahrer_ids as $f_id) {
                if (empty($f_id)) continue; 
                
                // Funktionsaufruf ob fahrer schon angemeldet ist
                if (isFahrerBereitsAngemeldet($pdo, $f_id, $rennen_id)) {
                    // Falls bereits gemeldet: Bisherige Inserts der Schleife rückgängig machen!
                    $pdo->rollBack();
                    $meldung = "Fehler: Einer der ausgewählten Fahrer ist bereits für dieses Rennen angemeldet!";
                    $duplikatGefunden = true;
                    break; // Schleife sofort abbrechen
                }
                
                // Eintragung mit ausgelagerter Funktion ausführen
                registriereFahrerInRennen($pdo, $f_id, $teamname, $rennen_id);
            }

            // Nur commiten und weiterleiten, wenn alles fehlerfrei durchlief
            if (!$duplikatGefunden) {
                $pdo->commit();
                // Verhindert das F5-Reload-Problem bei POST-Requests
                header("Location: rennanmeldung.php?success=1");
                exit;
            }
        }
    }

// Fehlerbehandlung
} catch (PDOException $e) {
    // Falls das Skript in den catch-Block springt 
    // und noch eine Transaktion offen ist, wird zurückgerollt!
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $meldung = "Fehler: " . htmlspecialchars($e->getMessage());
}


// Erfolgsmeldung nach sicherer Weiterleitung via GET abfangen
if (isset($_GET['success'])) {
    $meldung = "Fahrer wurden erfolgreich für das Rennen angemeldet!";
}

// Initialisierung der GUI-Variablen
$anzahlFahrer = isset($_POST['anzahl_fahrer']) ? intval($_POST['anzahl_fahrer']) : 0;
$gewaehltesRennen = isset($_POST['rennen_id']) ? intval($_POST['rennen_id']) : 0;

// Obergrenze greift ein, falls manipuliert wurde
if ($anzahlFahrer > $maxFahrerImTeam) {
    $anzahlFahrer = $maxFahrerImTeam; 
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Rennen Anmeldung</title>
</head>
<body>

    <h1>Fahrer zu Rennen anmelden</h1>
    <!-- Nutzt den GET-Parameter 'success' um die Farbe zu steuern -->
    <?php if (!empty($meldung)): ?>
        <p style="color: <?= isset($_GET['success']) ? 'green' : 'red' ?>; font-weight: bold;">
            <?= $meldung ?>
        </p>
    <?php endif; ?>

    <!-- Rennen und Anzahl wählen -->
    <!-- Wird angezeigt, wenn das Formular frisch geladen wird -->
    <?php if (($anzahlFahrer === 0 || $gewaehltesRennen === 0) && !isset($_GET['success'])): ?>
        
        <!-- XSS-Schutz durch htmlspecialchars -->
        <p>Willkommen, Teamchef von Team <strong><?= htmlspecialchars($teamname) ?></strong>.</p>
        <p>Ihr Team hat aktuell <strong><?= $maxFahrerImTeam ?></strong> registrierte(n) Fahrer.</p>
        
        <!-- POST für sichere Datenübergabe -->
        <form method="post" action="rennanmeldung.php">
            <label for="rennen_id">Zukünftiges Rennen auswählen:</label><br>
            
            <!-- COMBOBOX FÜR RENNEN -->
            <select name="rennen_id" id="rennen_id" required>
                <option value="">-- Bitte Rennen wählen --</option>
                <!-- foreach-Schleife durchläuft das Array der Renndaten -->
                <?php foreach ($zukuenftigeRennen as $r): ?>
                    <option value="<?= $r['ID'] ?>">
                        <!-- Datumskonvertierung für schönere Anzeige -->
                        <?= htmlspecialchars($r['Name']) ?> (<?= date('d.m.Y', strtotime($r['Datum'])) ?>) - Startort: <?= htmlspecialchars($r['Startort']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br><br>

            <label for="anzahl_fahrer">Wie viele Fahrer möchten Sie anmelden?</label><br>
            
            <input type="number" 
                   name="anzahl_fahrer" 
                   id="anzahl_fahrer" 
                   min="1" 
                   max="<?= $maxFahrerImTeam ?>" 
                   placeholder="Max. <?= $maxFahrerImTeam ?>"
                   style="width: 100px;" 
                   required>
            <br><br>

            <input type="submit" value="Weiter zur Fahrerauswahl">
        </form>

    <!-- Dynamische Tabelle für Fahrerauswahl -->
    <!-- Wird angezeigt, wenn Anzahl und Rennen per POST übermittelt wurden -->
    <?php elseif (!isset($_GET['success'])): ?>

        <p>Bitte weisen Sie den Zeilen die gewünschten Fahrer Ihres Teams zu:</p>
        
        <form method="post" action="rennanmeldung.php">
            <!-- Versteckte Felder zum Durchschleifen des Zustands (Skript Kap. 3.8.2) -->
            <input type="hidden" name="action" value="save_registration">
            <input type="hidden" name="rennen_id" value="<?= $gewaehltesRennen ?>">

           <!-- Dynamische Tabelle -->
           <table border="1" cellpadding="5">
                <tr>
                    <th>Zeile</th>
                    <th>Fahrer auswählen (ID & Name)</th>
                </tr>
                
                <!-- FOR-Schleife erzeugt genau so viele Zeilen wie gewünscht -->
                <?php for ($i = 1; $i <= $anzahlFahrer; $i++): ?>
                <tr>
                    <td>Fahrer <?= $i ?>:</td>
                    <td>          
                        <select name="fahrer_ids[]" required>
                            <option value="">-- Bitte Fahrer wählen --</option>
                            <?php foreach ($teamFahrer as $fahrer): ?>
                                <option value="<?= $fahrer['Mitarbeiter_ID'] ?>">
                                    ID: <?= $fahrer['Mitarbeiter_ID'] ?> | <?= htmlspecialchars($fahrer['Vorname'] . " " . $fahrer['Nachname']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <?php endfor; ?>
                </table>
            <br>

            <input type="submit" value="Fahrer verbindlich anmelden">
            | <a href="rennanmeldung.php">Abbrechen und zurück</a>
        </form>

    <!-- Erfolgsansicht -->
    <?php else: ?>
        <p><a href="rennanmeldung.php">Weitere Fahrer anmelden</a></p>
    <?php endif; ?>

    <hr>
    <p>
        <a href="rennanmeldung_kopieren.php">Rennanmeldungen kopieren </a>
    </p>

    <!-- Reine Link-basierte Navigation (Vorgabe Aufgabenblatt) -->
    <p><a href="teamchefmenu.php">Zurück zum Hauptmenü</a></p>

</body>
</html>
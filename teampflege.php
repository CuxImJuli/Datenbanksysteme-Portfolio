<?php
//Author: Juri Schröder
//Menü für das Anlegen, Bearbeiten und Löschen von Fahrern
session_start();
require_once __DIR__ . '/process.php';
require_once __DIR__ . '/teampflege.inc.php'; // Bindet die neuen SQL-Funktionen ein

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
$editData = [
    'Mitarbeiter_ID' => 0, 
    'Vorname' => '', 'Nachname' => '', 'Vorwahl' => '', 
    'Nummer' => '', 'PLZ' => '', 'ORT' => '', 'Strasse' => '', 'Hausnr' => ''
];

try {
    $pdo = connectToDatabase();

    //Teamname über Funktion holen
    $teamname = getTeamnameByLogin($pdo, $loginname);
    
    //Sicherheits-Check bei leeren Teams
    if (!$teamname) {
        die("Fehler: Ihrem Benutzerkonto ist kein Team zugeordnet.");
    }

    // Falls "Bearbeiten" geklickt wurde: Daten über Funktion laden
    if (isset($_GET['edit_id'])) {
        $row = getFahrerEditData($pdo, intval($_GET['edit_id']), $teamname);
        if ($row) {
            $editData = $row;
        }
    } 

    // Verarbeitung beim Absenden
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        if (isset($_POST['action']) && $_POST['action'] === 'delete') {
            // Fahrer löschen mit Funktion
            $delete_id = intval($_POST['delete_id']); 
            deleteFahrer($pdo, $delete_id, $teamname);
            
            header("Location: teampflege.php?deleted=1");
            exit;
        } else {
            //Fahrer speichern/neu anlegen mit funktionsaufruf
            //trim() um das speichern von nur Leerzeichen zu verhindern
            $vorname   = isset($_POST['vorname']) ? trim($_POST['vorname']) : '';
            $nachname  = isset($_POST['nachname']) ? trim($_POST['nachname']) : '';
            $vorwahl   = isset($_POST['vorwahl']) ? trim($_POST['vorwahl']) : '';
            $nummer    = isset($_POST['nummer']) ? trim($_POST['nummer']) : '';
            $plz       = isset($_POST['plz']) ? trim($_POST['plz']) : '';
            $ort       = isset($_POST['ort']) ? trim($_POST['ort']) : '';
            $strasse   = isset($_POST['strasse']) ? trim($_POST['strasse']) : '';
            $hausnr    = isset($_POST['hausnr']) ? trim($_POST['hausnr']) : '';
            $m_id      = isset($_POST['m_id']) ? intval($_POST['m_id']) : 0;
            //vorname und nachname müssen ausgefüllt sein
            if ($vorname === "" || $nachname === "") {
                $meldung = "Fehler: Vorname und Nachname sind Pflichtfelder!";
            } else {
                //Aufruf funktion zum speichern
                anlegenAnpassenFahrer($pdo, $m_id, $teamname, $vorname, $nachname, $vorwahl, $nummer, $plz, $ort, $strasse, $hausnr);
                //get request für das F5-Problem
                header("Location: teampflege.php?success=1");
                exit;
            }
        }
    }

    // URL-Parameter für Erfolgsmeldungen auswerten
    if (isset($_GET['success'])) {
        $meldung = "Erfolgreich gespeichert!";
    } elseif (isset($_GET['deleted'])) {
        $meldung = "Fahrer erfolgreich gelöscht!";
    }

    //Liste für die Tabelle über Funktion laden
    $alleFahrer = getAlleFahrer($pdo, $teamname);

} catch (PDOException $e) {
    $meldung = "Fehler: " . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Teampflege</title>
    <style>
        /* Buttons nebeneinander*/
        .action-form { display: inline-block; margin: 0; }
    </style>
</head>
<body>

    <h1>Fahrermanagement: <?= htmlspecialchars($teamname) ?></h1>
    <?php if (!empty($meldung)): ?>
        <p style="color: <?= (strpos($meldung, 'Fehler') === 0) ? 'red' : 'green' ?>; font-weight: bold; border: 1px solid currentColor; padding: 10px;">
            <?= $meldung ?>
        </p>
    <?php endif; ?>
        <!-- Eingabefelder -->
    <form method="post" action="teampflege.php">
        <input type="hidden" name="m_id" value="<?= htmlspecialchars((string)$editData['Mitarbeiter_ID']) ?>">
        Vorname:<br>
        <input type="text" name="vorname" value="<?= htmlspecialchars($editData['Vorname']) ?>" required><br>
        Nachname:<br>
        <input type="text" name="nachname" value="<?= htmlspecialchars($editData['Nachname']) ?>" required><br>
        Vorwahl:<br>
        <input type="text" name="vorwahl" value="<?= htmlspecialchars($editData['Vorwahl']) ?>"><br>
        Nummer:<br>
        <input type="text" name="nummer" value="<?= htmlspecialchars($editData['Nummer']) ?>"><br>
        Strasse:<br>
        <input type="text" name="strasse" value="<?= htmlspecialchars($editData['Strasse']) ?>"><br>
        Hausnr:<br>
        <input type="text" name="hausnr" value="<?= htmlspecialchars($editData['Hausnr']) ?>"><br>
        PLZ:<br>
        <input type="text" name="plz" value="<?= htmlspecialchars($editData['PLZ']) ?>"><br>
        Ort:<br>
        <input type="text" name="ort" value="<?= htmlspecialchars($editData['ORT']) ?>"><br><br>
        <input type="submit" value="<?= $editData['Mitarbeiter_ID'] > 0 ? 'Änderungen speichern' : 'Neu anlegen' ?>">
        
        <?php if ($editData['Mitarbeiter_ID'] > 0): ?>
            | <a href="teampflege.php">Abbrechen (Neuen Fahrer anlegen)</a>
        <?php endif; ?>
    </form>

    <hr>

    <h3>Alle Fahrer im Team</h3>
    <table border="1" cellpadding="5" style="border-collapse: collapse;">
        <!-- Tabelle für die Fahrer -->
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($alleFahrer)): ?>
                <tr>
                    <td colspan="3">Noch keine Fahrer vorhanden.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($alleFahrer as $f): ?>
                <tr>
                    <td><?= htmlspecialchars((string)$f['Mitarbeiter_ID']) ?></td>
                    <td><?= htmlspecialchars($f['Vorname'] . " " . $f['Nachname']) ?></td>
                    <td>
                        <!-- Formular für Bearbeiten (GET) -->
                        <form method="get" action="teampflege.php" class="action-form">
                            <input type="hidden" name="edit_id" value="<?= htmlspecialchars((string)$f['Mitarbeiter_ID']) ?>">
                            <input type="submit" value="Bearbeiten">
                        </form>
                        
                        <!--Formular für Löschen (POST) -->
                        <form method="post" action="teampflege.php" class="action-form">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="delete_id" value="<?= htmlspecialchars((string)$f['Mitarbeiter_ID']) ?>">
                            <input type="submit" value="Löschen">
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <br>
    <p><a href="teamchefmenu.php">Zurück zum Teamchefmenü</a></p>

</body>
</html>
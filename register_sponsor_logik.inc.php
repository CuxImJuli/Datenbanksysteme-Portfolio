<?php
// Autor: Julian Ploch
// Beschreibung: Registrierungslogik für Sponsoren (wird per require in index.php eingebunden)

$fehler_reg = "";
$erfolg_reg = "";

// Nur verarbeiten wenn das Sponsor-Registrierungsformular abgeschickt wurde
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_action']) && $_POST['form_action'] === 'sponsor_register') {

    // htmlspecialchars() gegen XSS; trim() entfernt ungewollte Leerzeichen
    $name      = htmlspecialchars(trim($_POST['name']));
    $passwort  = $_POST['passwort'];
    $passwort2 = $_POST['passwort2'];
    $budget    = $_POST['budget'] ?? '';

    // Eingabevalidierung: alle Felder müssen ausgefüllt sein
    if (empty($name) || empty($passwort) || empty($passwort2) || $budget === '') {
        $fehler_reg = "Bitte alle Felder ausfüllen.";

    // Passwörter müssen übereinstimmen
    } elseif ($passwort !== $passwort2) {
        $fehler_reg = "Die Passwörter stimmen nicht überein.";

    // Mindestlänge für das Passwort
    } elseif (strlen($passwort) < 6) {
        $fehler_reg = "Das Passwort muss mindestens 6 Zeichen lang sein.";

    // Budget muss eine positive Zahl sein
    } elseif (!is_numeric($budget) || (float)$budget <= 0) {
        $fehler_reg = "Bitte ein gültiges Budget eingeben.";

    } else {

        // Prüfen ob der Name bereits vergeben ist
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Sponsor WHERE Name = ?");
        $stmt->execute([$name]);
        $anzahl = $stmt->fetchColumn();

        if ($anzahl > 0) {
            $fehler_reg = "Ein Sponsor mit diesem Namen existiert bereits.";
        } else {
            // Passwort mit bcrypt hashen; PASSWORD_DEFAULT wählt automatisch den sichersten Algorithmus
            $passwortHash = password_hash($passwort, PASSWORD_DEFAULT);

            // RestBudget wird initial gleich dem Gesamtbudget gesetzt
            $stmt = $pdo->prepare("INSERT INTO Sponsor (Name, Passwort, Budget, RestBudget) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $passwortHash, (float)$budget, (float)$budget]);

            // PRG: Weiterleitung verhindert doppelte Registrierung bei Seitenaktualisierung
            header("Location: index.php?erfolg=registrierung");
            exit();
        }
    }
}

// Erfolgsmeldung nach Weiterleitung per GET-Parameter anzeigen
if (isset($_GET['erfolg']) && $_GET['erfolg'] === 'registrierung') {
    $erfolg_reg = "Registrierung erfolgreich! Sie können sich jetzt anmelden.";
}
?>
<?php
// Autor: Julian Ploch
// Beschreibung: Login-Logik für Sponsoren (wird per require in sponsor.php eingebunden)

$fehler_login = "";
$erfolg_login = "";

// Nur verarbeiten wenn das Sponsor-Login-Formular abgeschickt wurde
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_action']) && $_POST['form_action'] === 'sponsor_login') {

    // htmlspecialchars() gegen XSS; trim() entfernt ungewollte Leerzeichen
    $name     = htmlspecialchars(trim($_POST['login_name']));
    $passwort = $_POST['login_passwort'];

    if (empty($name) || empty($passwort)) {
        $fehler_login = "Bitte alle Felder ausfüllen.";

    } else {

        // Prepared Statement verhindert SQL-Injection
        $stmt = $pdo->prepare("SELECT * FROM Sponsor WHERE Name = ?");
        $stmt->execute([$name]);
        $sponsor = $stmt->fetch();

        // password_verify() prüft gegen den gespeicherten bcrypt-Hash
        if ($sponsor && password_verify($passwort, $sponsor['Passwort'])) {
            $_SESSION['rolle'] = 'sponsor';
            $_SESSION['name']  = $sponsor['Name'];
            $_SESSION['id']    = $sponsor['SponsorID'];

            header("Location: dashboard_sponsor.php");
            exit();

        } else {
            // Bewusst vage: kein Hinweis ob Name oder Passwort falsch ist
            $fehler_login = "Name oder Passwort falsch.";
        }
    }
}

// Erfolgsmeldung nach Weiterleitung per GET-Parameter setzen
if (isset($_GET['erfolg']) && $_GET['erfolg'] === 'login') {
    $erfolg_login = "Anmeldung erfolgreich! Willkommen, " . htmlspecialchars($_SESSION['name']) . "!";
}
?>
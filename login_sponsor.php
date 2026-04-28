<?php
// Autor: Julian Ploch
// Beschreibung: Anmeldung eins Sponsors

$fehler_login = "";
$erfolg_login = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['anmelden'])) {

    $name     = htmlspecialchars(trim($_POST['login_name']));
    $passwort = $_POST['login_passwort'];

    if (empty($name) || empty($passwort)) {
        $fehler_login = "Bitte alle Felder ausfüllen.";

    } else {
        // SQL-Injektion Schutz
        $stmt = $pdo->prepare("SELECT * FROM Sponsor WHERE Name = ?");
        $stmt->execute([$name]);
        $sponsor = $stmt->fetch();

        if ($sponsor && password_verify($passwort, $sponsor['Passwort'])) {
            session_start();
            $_SESSION['rolle'] = 'sponsor';
            $_SESSION['name']  = $sponsor['Name'];
            $_SESSION['id']    = $sponsor['SponsorID'];
            $erfolg_login = "Anmeldung erfolgreich! Willkommen, " . htmlspecialchars($sponsor['Name']) . "!";
        } else {
            $fehler_login = "Name oder Passwort falsch.";
        }
    }
}
?>
<?php
// Autor: Julian Ploch
// Beschreibung: Loginlogik für Sponsoren

$fehler_login = "";
$erfolg_login = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_action']) && $_POST['form_action'] === 'sponsor_login') {

    $name     = htmlspecialchars(trim($_POST['login_name']));
    $passwort = $_POST['login_passwort'];

    if (empty($name) || empty($passwort)) {
        $fehler_login = "Bitte alle Felder ausfüllen.";

    } else {

        $stmt = $pdo->prepare("SELECT * FROM Sponsor WHERE Name = ?");
        $stmt->execute([$name]);
        $sponsor = $stmt->fetch();

        if ($sponsor && password_verify($passwort, $sponsor['Passwort'])) {
            $_SESSION['rolle'] = 'sponsor';
            $_SESSION['name']  = $sponsor['Name'];
            $_SESSION['id']    = $sponsor['SponsorID'];

            header("Location: dashboard_sponsor.php");
            exit();

        } else {
            $fehler_login = "Name oder Passwort falsch.";
        }
    }
}

if (isset($_GET['erfolg']) && $_GET['erfolg'] === 'login') {
    $erfolg_login = "Anmeldung erfolgreich! Willkommen, " . htmlspecialchars($_SESSION['name']) . "!";
}
?>
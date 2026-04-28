<?php
// Autor : Julian Ploch
// Beschreibung: Registrierung eines Sponsors

$fehler_reg = "";
$erfolg_reg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrieren'])) {

    $name      = htmlspecialchars(trim($_POST['name']));
    $passwort  = $_POST['passwort'];
    $passwort2 = $_POST['passwort2'];

    if (empty($name) || empty($passwort) || empty($passwort2)) {
        $fehler_reg = "Bitte alle Felder ausfüllen.";

    } elseif ($passwort !== $passwort2) {
        $fehler_reg = "Die Passwörter stimmen nicht überein.";

    } elseif (strlen($passwort) < 6) {
        $fehler_reg = "Das Passwort muss mindestens 6 Zeichen lang sein.";

    } else {

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Sponsor WHERE Name = ?");
        $stmt->execute([$name]);
        $anzahl = $stmt->fetchColumn();

        if ($anzahl > 0) {
            $fehler_reg = "Ein Sponsor mit diesem Namen existiert bereits.";
        } else {
            $passwortHash = password_hash($passwort, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO Sponsor (Name, Passwort) VALUES (?, ?)");
            $stmt->execute([$name, $passwortHash]);
            $erfolg_reg = "Registrierung erfolgreich!";
        }
    }
}
?>
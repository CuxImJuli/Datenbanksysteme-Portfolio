<?php
// Autor: Julian Ploch
// Beschreibung: Registrierungslogik für Sponsoren

$fehler_reg = "";
$erfolg_reg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_action']) && $_POST['form_action'] === 'sponsor_login') {

    $name      = htmlspecialchars(trim($_POST['name']));
    $passwort  = $_POST['passwort'];
    $passwort2 = $_POST['passwort2'];
    $budget    = $_POST['budget'] ?? '';

    if (empty($name) || empty($passwort) || empty($passwort2) || $budget === '') {
        $fehler_reg = "Bitte alle Felder ausfüllen.";

    } elseif ($passwort !== $passwort2) {
        $fehler_reg = "Die Passwörter stimmen nicht überein.";

    } elseif (strlen($passwort) < 6) {
        $fehler_reg = "Das Passwort muss mindestens 6 Zeichen lang sein.";

    } elseif (!is_numeric($budget) || (float)$budget <= 0) {
        $fehler_reg = "Bitte ein gültiges Budget eingeben.";

    } else {

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Sponsor WHERE Name = ?");
        $stmt->execute([$name]);
        $anzahl = $stmt->fetchColumn();

        if ($anzahl > 0) {
            $fehler_reg = "Ein Sponsor mit diesem Namen existiert bereits.";
        } else {
            $passwortHash = password_hash($passwort, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO Sponsor (Name, Passwort, Budget, RestBudget) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $passwortHash, (float)$budget, (float)$budget]);

           header("Location: index.php?erfolg=registrierung");
            exit();
        }
    }
}

if (isset($_GET['erfolg']) && $_GET['erfolg'] === 'registrierung') {
    $erfolg_reg = "Registrierung erfolgreich! Sie können sich jetzt anmelden.";
}
?>
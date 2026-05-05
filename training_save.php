<?php
/**
 * Author: Noah S. Kipp
 */
// Starten der Session und Einbinden der notwendigen Funktionen
session_start();
require_once __DIR__ . '/process.php';

// Überprüfen, ob der Benutzer eingeloggt ist
if (empty($_SESSION['loginname'])) {
    header("Location: teamlogin.php");
    exit;
}
 
// Überprüfen, ob die Anfrage eine POST-Anfrage ist, anschließende Verweisung auf das Teamchef-Menü
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: teamchefmenu.php");
    exit;
}

// Speichern der $_POST-Werte in Variablen und anschließendes Trimmen der Eingaben
$mitarbeiterID = trim($_POST['mitarbeiterID'] ?? '');
$teamname      = trim($_POST['teamname']      ?? '');
$datum         = trim($_POST['datum']         ?? '');
$kilometer     = $_POST['kilometer']          ?? '';
$zielname      = trim($_POST['zielname']      ?? '');

// Überprüfen, ob die notwendigen Werte vorhanden sind, ansonsten Verweisung auf das Teamchef-Menü
if (!$mitarbeiterID || !$teamname || !$datum || $kilometer === '' || !$zielname) {
    header("Location: teamchefmenu.php?status=fehler");
    exit;
}

// Ausführen der Speicherung und anschließende Verweisung auf das Teamchef-Menü
try {
    $pdo = connectToDatabase();

    $stmt = $pdo->prepare(
        "INSERT INTO Training (Mitarbeiter_ID, Teamname, Datum, Kilometer, Zielname)
         VALUES (:mitarbeiterID, :teamname, :datum, :kilometer, :zielname)"
    );
    $stmt->execute([
        ':mitarbeiterID' => $mitarbeiterID,
        ':teamname'      => $teamname,
        ':datum'         => $datum,
        ':kilometer'     => (float) $kilometer,
        ':zielname'      => $zielname,
    ]);

    header("Location: teamchefmenu.php?status=ok");
} catch (PDOException $e) {
    if ($e->getCode() == 45001) {
        header("Location: teamchefmenu.php?status=fehler_doppelt");
    } else {
        error_log("Training insert error: " . $e->getMessage());
        header("Location: teamchefmenu.php?status=fehler");
    }
}
exit;
?>

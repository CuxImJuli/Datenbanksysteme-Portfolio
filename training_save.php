<?php
/**
 * Author: Noah S. Kipp
 */
session_start();
require_once __DIR__ . '/process.php';

if (empty($_SESSION['loginname'])) {
    header("Location: teamlogin.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: TeamchefMenu.php");
    exit;
}

$mitarbeiterID = trim($_POST['mitarbeiterID'] ?? '');
$teamname      = trim($_POST['teamname']      ?? '');
$datum         = trim($_POST['datum']         ?? '');
$kilometer     = $_POST['kilometer']          ?? '';
$zielname      = trim($_POST['zielname']      ?? '');

if (!$mitarbeiterID || !$teamname || !$datum || $kilometer === '' || !$zielname) {
    header("Location: TeamchefMenu.php?status=fehler");
    exit;
}

try {
    $pdo = connectToDatabase();

    $stmt = $pdo->prepare(
        "INSERT INTO Training (`Mitarbeiter-ID`, Teamname, Datum, Kilometer, Zielname)
         VALUES (:mitarbeiterID, :teamname, :datum, :kilometer, :zielname)"
    );
    $stmt->execute([
        ':mitarbeiterID' => $mitarbeiterID,
        ':teamname'      => $teamname,
        ':datum'         => $datum,
        ':kilometer'     => (float) $kilometer,
        ':zielname'      => $zielname,
    ]);

    header("Location: TeamchefMenu.php?status=ok");
} catch (PDOException $e) {
    error_log("Training insert error: " . $e->getMessage());
    header("Location: TeamchefMenu.php?status=fehler");
}
exit;
?>

<?php
session_start();
require_once __DIR__ . '/process.php';

if (empty($_SESSION['loginname'])) {
    header("Location: teamlogin.php");
    exit;
}

$loginname = $_SESSION['loginname'];

try {
    $pdo = connectToDatabase();

    $stmt = $pdo->prepare("SELECT Teamname FROM Team WHERE Loginname = :loginname LIMIT 1");
    $stmt->execute([':loginname' => $loginname]);
    $teamname = $stmt->fetchColumn();

    if (!$teamname) {
        die("Kein Team für diesen Teamchef gefunden.");
    }

    $stmt = $pdo->prepare(
        "SELECT `Mitarbeiter-ID`, Teamname FROM Fahrer WHERE Teamname = :teamname ORDER BY `Mitarbeiter-ID`"
    );
    $stmt->execute([':teamname' => $teamname]);
    $fahrer = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT Zielname FROM Trainingsziel ORDER BY Zielname");
    $stmt->execute();
    $ziele = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Datenbankfehler: " . htmlspecialchars($e->getMessage()));
}

include 'TeamchefMenuView.php';
?>

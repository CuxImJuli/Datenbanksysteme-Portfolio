<?php
require_once __DIR__ . '/process.php';

// Header um CORS zu umgehen
header("Access-Control-Allow-Origin: https://dbsnk.kirchbergnet.de");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname     = $_POST['fname'];
    $lname     = $_POST['lname'];
    $teamname  = $_POST['teamname'];
    $loginname = $_POST['loginname'];
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $pdo = connectToDatabase();

        if (checkTeamExists($pdo, $teamname)) {
            echo "Team existiert bereits";
            exit;
        }

        registerTeam($pdo, $loginname, $fname, $lname, $password, $teamname);
        echo "Ihr Team wurde erfolgreich angelegt";

    } catch (PDOException $e) {
        echo "Fehler: " . $e->getMessage();
    }
} else {
    echo "Bitte Daten überprüfen und erneut versuchen";
}
?>

<?php
require_once __DIR__ . '/process.php';

// Header um CORS zu umgehen
header("Access-Control-Allow-Origin: https://dbsnk.kirchbergnet.de");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo = connectToDatabase();

        if (checkTeamExists($pdo, $_POST['teamname'])) {
            echo "Team existiert bereits";
            exit;
        }

        registerUser($pdo, 'team', [
            'loginname' => $_POST['loginname'],
            'fname'     => $_POST['fname'],
            'lname'     => $_POST['lname'],
            'password'  => $_POST['password'],
            'teamname'  => $_POST['teamname'],
        ]);

        echo "Ihr Team wurde erfolgreich angelegt";

    } catch (PDOException $e) {
        echo "Fehler: " . $e->getMessage();
    }
} else {
    echo "Bitte Daten überprüfen und erneut versuchen";
}
?>

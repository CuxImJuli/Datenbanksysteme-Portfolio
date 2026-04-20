<?php
require_once __DIR__ . '/process.php';

// Header um CORS zu umgehen
header("Access-Control-Allow-Origin: https://dbsnk.kirchbergnet.de");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

try {
    $pdo = connectToDatabase();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $loginname = $_POST['loginname'];
        $password  = $_POST['password'];

        if (validatePasswort($loginname, $password, $pdo)) {
            echo "Login erfolgreich";
        } else {
            echo "Login fehlgeschlagen";
        }
    }
} catch (PDOException $e) {
    echo "Fehler: " . $e->getMessage();
}
?>

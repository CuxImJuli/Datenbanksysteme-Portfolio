<?php

function connectToDatabase(string $host, string $dbname, string $username, string $dbpass): PDO {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    return new PDO($dsn, $username, $dbpass, $options);
}

// Header um CORS zu umgehen
header("Access-Control-Allow-Origin: https://dbsnk.kirchbergnet.de");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

$env = parse_ini_file('.env'); 

function validatePasswort(string $loginname, string $password, PDO $pdo): bool {
            $stmt = $pdo->prepare("SELECT Loginname, Passwort FROM Teamchef WHERE Loginname = :loginname");
            $stmt->execute([':loginname' => $loginname]);
            $user = $stmt->fetch();
            if ($user && password_verify($password, $user['Passwort'])) {
                return true;
            }
            return false;
        }

try{
    $pdo = connectToDatabase('localhost', 'gruppe21', 'gruppe21', $env['DBPASS']);
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $loginname = $_POST['loginname'];
        $password  = $_POST['password'];

        if (validatePasswort($loginname, $password, $pdo)) {
            echo "Login erfolgreich";
        } else {
            echo "Login fehlgeschlagen";
        }
    }} 
    catch (PDOException $e) {
        echo "Fehler: " . $e->getMessage();
    }

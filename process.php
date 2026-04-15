<?php

header("Access-Control-Allow-Origin: https://dbsnk.kirchbergnet.de");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

$env = parse_ini_file('.env');

$host = 'localhost';
$dbname = 'gruppe21';
$username = 'gruppe21';
$password = $env['DBPASS'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $teamname = $_POST['teamname'];
    $loginname = $_POST['loginname'];
    $teamlead = $_POST['teamchef'];
    $password = $_POST['password'];

    try {
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        $pdo = new PDO($dsn, $username, $password, $options);

        $sql = "INSERT INTO Teamchef (Loginname, Vorname, Nachname, Passwort) VALUES (:loginname, :fname, :lname, :password)";
        $sql1 = "INSERT INTO Team (Loginname, Teamname) VALUES (:teamname, :loginname)";
        $stmt = $pdo->prepare($sql);
        $stmt1 = $pdo->prepare($sql1);

        $stmt->execute([
            ':loginname' => $loginname,
            ':fname' => $fname,
            ':lname' => $lname,
            ':password' => $password
            
        ]);
        
        $stmt1->execute([
            ':loginname' => $loginname,
            ':teamname' => $teamname
            
        ]);

        echo "Ihr Team wurde erfolgreich angelegt";

    } catch (PDOException $e) {
        echo "Fehler: " . $e->getMessage();
    }
} else {
    echo "Bitte Daten überprüfen und erneut versuchen";
}
?>
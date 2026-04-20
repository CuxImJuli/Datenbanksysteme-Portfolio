<?php
// Header um CORS zu umgehen
header("Access-Control-Allow-Origin: https://dbsnk.kirchbergnet.de");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

$env = parse_ini_file('.env');

// Stellt eine Verbindung zur Datenbank her und gibt das PDO-Objekt zurück
function connectToDatabase(string $host, string $dbname, string $username, string $dbpass): PDO {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    return new PDO($dsn, $username, $dbpass, $options);
}

// Überprüft, ob ein Team mit dem angegebenen Namen bereits existiert
function checkTeamExists(PDO $pdo, string $teamname): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Team WHERE Teamname = :teamname");
    $stmt->execute([':teamname' => $teamname]);
    return $stmt->fetchColumn() > 0;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname     = $_POST['fname'];
    $lname     = $_POST['lname'];
    $teamname  = $_POST['teamname'];
    $loginname = $_POST['loginname'];
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $pdo = connectToDatabase('localhost', 'gruppe21', 'gruppe21', $env['DBPASS']);

        if (checkTeamExists($pdo, $teamname)) {
            echo "Team existiert bereits";
            exit;
        }

        // Schutz vor SQL-Injection durch Prepared Statements
        $stmt = $pdo->prepare(
            "INSERT INTO Teamchef (Loginname, Vorname, Nachname, Passwort) VALUES (:loginname, :fname, :lname, :password)"
        );
        $stmt->execute([
            ':loginname' => $loginname,
            ':fname'     => $fname,
            ':lname'     => $lname,
            ':password'  => $password,
        ]);

        $stmt1 = $pdo->prepare(
            "INSERT INTO Team (Loginname, Teamname) VALUES (:loginname, :teamname)"
        );
        $stmt1->execute([
            ':loginname' => $loginname,
            ':teamname'  => $teamname,
        ]);

        echo "Ihr Team wurde erfolgreich angelegt";

    } catch (PDOException $e) {
        echo "Fehler: " . $e->getMessage();
    }
} else {
    echo "Bitte Daten überprüfen und erneut versuchen";
}
?>
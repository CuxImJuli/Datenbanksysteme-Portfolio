<?php

// Stellt eine Verbindung zur Datenbank her und gibt das PDO-Objekt zurück
function connectToDatabase(): PDO {
    $env = parse_ini_file(__DIR__ . '/.env');
    $dsn = "mysql:host=localhost;dbname=gruppe21;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    return new PDO($dsn, 'gruppe21', $env['DBPASS'], $options);
}

// Überprüft, ob ein Team mit dem angegebenen Namen bereits existiert
function checkTeamExists(PDO $pdo, string $teamname): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Team WHERE Teamname = :teamname");
    $stmt->execute([':teamname' => $teamname]);
    return $stmt->fetchColumn() > 0;
}

// Überprüft, ob das angegebene Passwort korrekt ist
function validatePasswort(string $loginname, string $password, PDO $pdo): bool {
    $stmt = $pdo->prepare("SELECT Loginname, Passwort FROM Teamchef WHERE Loginname = :loginname");
    $stmt->execute([':loginname' => $loginname]);
    $user = $stmt->fetch();
    return $user && password_verify($password, $user['Passwort']);
}
?>
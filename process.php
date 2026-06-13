<?php
/**
 * Author: Noah S. Kipp
 */

// Funktion zum Datenbankverbindung aufbauen
function connectToDatabase()
{
    $env = parse_ini_file(__DIR__ . '/.env');
    $dsn = "mysql:host=" . $env['DBHOST'] . ";dbname=" . $env['DBNAME'] . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    return new PDO($dsn, $env['DBUSER'], $env['DBPASS'], $options);
}

// Funktion zum Passwort überprüfen
function validatePasswort($loginname, $password, $pdo)
{
    $stmt = $pdo->prepare("SELECT Loginname, Passwort FROM Teamchef WHERE Loginname = :loginname");
    $stmt->execute([':loginname' => $loginname]);
    $user = $stmt->fetch();
    return $user && password_verify($password, $user['Passwort']);
}

// Überprüft, ob ein Team mit dem angegebenen Namen bereits existiert
function checkTeamExists($pdo, $teamname)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Team WHERE Teamname = :teamname");
    $stmt->execute([':teamname' => $teamname]);
    return $stmt->fetchColumn() > 0;
}

// Überprüft, ob ein Loginname für einen Teamchef bereits existiert
function checkLoginExists($pdo, $loginname)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Teamchef WHERE Loginname = :loginname");
    $stmt->execute([':loginname' => $loginname]);
    return $stmt->fetchColumn() > 0;
}

/*
Sucht einen Rennveranstalter anhand seines Namens
Autor: Linda Maaß
*/
function findOrganizer(PDO $pdo, string $name): array|false
{
    $statement = $pdo->prepare("SELECT * FROM Rennveranstalter WHERE Name = :name");
    $statement->execute([':name' => $name]);
    return $statement->fetch(PDO::FETCH_ASSOC);
}



?>
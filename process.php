<?php
/**
 * Author: Noah S. Kipp
 */

// Funktion zum Datenbankverbindung aufbauen
function connectToDatabase() {
    $env = parse_ini_file(__DIR__ . '/.env');
    $dsn = "mysql:host=localhost;dbname=gruppe21;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    return new PDO($dsn, 'gruppe21', $env['DBPASS'], $options);
}

// Funktion zum Passwort überprüfen
function validatePasswort($loginname, $password, $pdo) {
    $stmt = $pdo->prepare("SELECT Loginname, Passwort FROM Teamchef WHERE Loginname = :loginname");
    $stmt->execute([':loginname' => $loginname]);
    $user = $stmt->fetch();
    return $user && password_verify($password, $user['Passwort']);
}

// Überprüft, ob ein Team mit dem angegebenen Namen bereits existiert
function checkTeamExists($pdo, $teamname) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Team WHERE Teamname = :teamname");
    $stmt->execute([':teamname' => $teamname]);
    return $stmt->fetchColumn() > 0;
}

// Kapselfunktion zur Registrierung von Nutzern 
function registerUser($pdo, $type, $data) {
    if (isset($data['password'])) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }
    try {
        switch ($type) {
            case 'team':
                $stmt = $pdo->prepare("CALL p_registerTeamWithChef_nsk(:loginname, :fname, :lname, :password, :teamname)");
                $stmt->execute([
                    ':loginname' => $data['loginname'],
                    ':fname'     => $data['fname'],
                    ':lname'     => $data['lname'],
                    ':password'  => $data['password'],
                    ':teamname'  => $data['teamname']
                ]);
                break;

            case 'veranstalter':
                $stmt = $pdo->prepare("INSERT INTO Rennveranstalter (Name, Passwort) VALUES (:name, :password)");
                $stmt->execute([
                    ':name'     => $data['name'],
                    ':password' => $data['password']
                ]);
                break;

            case 'sponsor':
                $stmt = $pdo->prepare("INSERT INTO Sponsor (SponsorID, Name, Passwort) VALUES (:id, :name, :password)");
                $stmt->execute([
                    ':id'       => $data['id'],
                    ':name'     => $data['name'],
                    ':password' => $data['password']
                ]);
                break;

            default:
                throw new Exception("Unbekannter Registrierungstyp: " . $type);
        }
    } catch (PDOException $e) {
        throw $e;
    }
}


?>
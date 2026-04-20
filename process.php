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

// Blanket Funktion zur Anlegung verschiedener Nutzer
function registerUser(PDO $pdo, string $type, array $data): void {
    if (isset($data['password'])) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }
    try {
        switch ($type) {
            case 'team':
                $stmt = $pdo->prepare("INSERT INTO Teamchef (Loginname, Vorname, Nachname, Passwort) 
                                       VALUES (:loginname, :fname, :lname, :password)");
                $stmt->execute([
                    ':loginname' => $data['loginname'],
                    ':fname'     => $data['fname'],
                    ':lname'     => $data['lname'],
                    ':password'  => $data['password']
                ]);

                $stmt1 = $pdo->prepare("INSERT INTO Team (Teamname, Loginname) VALUES (:teamname, :loginname)");
                $stmt1->execute([
                    ':teamname'  => $data['teamname'],
                    ':loginname' => $data['loginname']
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
        die("Fehler bei der Registrierung: " . $e->getMessage());
    }
}
?>
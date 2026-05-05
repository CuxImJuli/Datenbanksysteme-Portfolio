<?php
/**
 * Author: Noah S. Kipp
 */

function connectToDatabase(): PDO {
    $env = parse_ini_file(__DIR__ . '/.env');
    $dsn = "mysql:host=localhost;dbname=gruppe21;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    return new PDO($dsn, 'gruppe21', $env['DBPASS'], $options);
}


function validatePasswort(string $loginname, string $password, PDO $pdo): bool {
    $stmt = $pdo->prepare("SELECT Loginname, Passwort FROM Teamchef WHERE Loginname = :loginname");
    $stmt->execute([':loginname' => $loginname]);
    $user = $stmt->fetch();
    return $user && password_verify($password, $user['Passwort']);
}

function registerUser(PDO $pdo, string $type, array $data): void {
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
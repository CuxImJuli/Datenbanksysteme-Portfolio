<?php
/**
 * Author: Noah S. Kipp
 */
// Starten der Session und Einbinden der notwendigen Funktionen
session_start();
require_once __DIR__ . '/process.php';

// Setzen der CORS-Header

$login_error_message = "";
$reg_message = "";

// Überprüfen, ob die Anfrage eine POST-Anfrage ist und anschließend Team-Login, ansonsten Team-Registrierung
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = connectToDatabase();

        if (isset($_POST['action']) && $_POST['action'] === 'team_login') {
            $loginname = trim($_POST['loginname'] ?? '');
            $password  = $_POST['password'] ?? '';

            if (validatePasswort($loginname, $password, $pdo)) {
                $_SESSION['loginname'] = $loginname;
                header("Location: teamchefmenu.php");
                exit;
            } else {
                $login_error_message = "Login fehlgeschlagen. Bitte erneut versuchen.";
            }
        } elseif (isset($_POST['action']) && $_POST['action'] === 'team_register') {
            if (checkTeamExists($pdo, $_POST['teamname'])) {
                $reg_message = "Team existiert bereits.";
            } else {
                registerUser($pdo, 'team', [
                    'loginname' => $_POST['loginname'],
                    'fname'     => $_POST['fname'],
                    'lname'     => $_POST['lname'],
                    'password'  => $_POST['password'],
                    'teamname'  => $_POST['teamname'],
                ]);
                $reg_message = "Ihr Team wurde erfolgreich angelegt!";
            }
        }
    } catch (PDOException $e) {
        if (isset($_POST['action']) && $_POST['action'] === 'team_login') {
            error_log("Login error: " . $e->getMessage());
            $login_error_message = "Ein Systemfehler ist aufgetreten.";
        } elseif (isset($_POST['action']) && $_POST['action'] === 'team_register') {
            if ($e->getCode() == 23000 || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062)) {
                $reg_message = "Team oder Loginname existiert bereits.";
            } else {
                $reg_message = "Fehler: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Anmeldung & Registrierung</title>
</head>
<body>
    <h1>Team Bereich</h1>
    <hr>
    
    <!-- Formulare zur Registrierung und Anmeldung von Teams, als Tabelle zur übersichtlichen Darstellung -->
    <table>
        <tr>
            <td style="vertical-align:top; padding-right:50px;">
                <h2>Team registrieren</h2>
                <?php if ($reg_message): ?>
                    <p><strong><?= htmlspecialchars($reg_message) ?></strong></p>
                <?php endif; ?>
                <form action="teamlogin.php" method="post">
                    <input type="hidden" name="action" value="team_register">
                    <label for="reg_fname">Teamchef Vorname:</label><br>
                    <input type="text" id="reg_fname" name="fname" required><br>
                    <label for="reg_lname">Teamchef Nachname:</label><br>
                    <input type="text" id="reg_lname" name="lname" required><br>
                    <label for="reg_teamname">Teamname:</label><br>
                    <input type="text" id="reg_teamname" name="teamname" required><br>
                    <label for="reg_loginname">Loginname:</label><br>
                    <input type="text" id="reg_loginname" name="loginname" required><br>
                    <label for="reg_password">Passwort:</label><br>
                    <input type="password" id="reg_password" name="password" required><br>
                    <br>
                    <input type="submit" value="Registrieren">
                </form>
            </td>

            <!-- TRENNLINIE -->
            <td style="border-left: 1px solid black; padding-right:50px;"></td>
            <td style="vertical-align:top; padding-left:50px;">
                <h2>Team anmelden</h2>
                <?php if ($login_error_message): ?>
                    <p><strong><?= htmlspecialchars($login_error_message) ?></strong></p>
                <?php endif; ?>
                <form action="teamlogin.php" method="post">
                    <input type="hidden" name="action" value="team_login">
                    <label for="login_loginname">Loginname:</label><br>
                    <input type="text" id="login_loginname" name="loginname" required><br><br>
                    <label for="login_password">Passwort:</label><br>
                    <input type="password" id="login_password" name="password" required><br><br>
                    <input type="submit" value="Einloggen">
                </form>
            </td>
        </tr>
    </table>
</body>
</html>

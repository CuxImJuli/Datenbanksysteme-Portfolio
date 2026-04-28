<?php
session_start();

include 'db.inc.php';
include 'register_sponsor.php';
include 'login_sponsor.php';
require_once __DIR__ . '/process.php';

header("Access-Control-Allow-Origin: https://dbsnk.kirchbergnet.de");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

$login_error_message = "";
$reg_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = connectToDatabase();

        if (isset($_POST['action']) && $_POST['action'] === 'team_login') {
            $loginname = trim($_POST['loginname'] ?? '');
            $password  = $_POST['password'] ?? '';

            if (validatePasswort($loginname, $password, $pdo)) {
                $_SESSION['loginname'] = $loginname;
                header("Location: TeamchefMenu.php");
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
            $reg_message = "Fehler: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Radrennen Verwaltung</title>
</head>
<body>

    <h1>Willkommen</h1>
    <hr>

    <!-- Tabelle damit beide Formulare nebeneinander stehen -->
    <table>
        <tr>

            <!-- LINKE SPALTE: Registrierung -->
            <td style="vertical-align:top; padding-right:50px;">
                <h2>Sponsor registrieren</h2>

                <?php if ($fehler_reg !== ""): ?>
                    <p style="color:red;"><strong>Fehler:</strong> <?= $fehler_reg ?></p>
                <?php endif; ?>

                <?php if ($erfolg_reg !== ""): ?>
                    <p style="color:green;"><strong><?= $erfolg_reg ?></strong></p>
                    <a href="login_sponsor.php">Jetzt anmelden</a>
                <?php else: ?>
                <form method="post" action="index.php">
                    <label>Name:</label><br>
                    <input type="text" name="name"
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                    <br><br>

                    <label>Passwort:</label><br>
                    <input type="password" name="passwort">
                    <br><br>

                    <label>Passwort wiederholen:</label><br>
                    <input type="password" name="passwort2">
                    <br><br>

                    <input type="submit" name="registrieren" value="Registrieren">
                </form>
                <?php endif; ?>
            </td>

            <!-- TRENNLINIE -->
            <td style="border-left: 1px solid black; padding-right:50px;"></td>

            <!-- RECHTE SPALTE: Login -->
            <td style="vertical-align:top; padding-left:50px;">
                <h2>Sponsor anmelden</h2>
                
                <?php if (isset($fehler_login) && $fehler_login !== ""): ?>
                    <p style="color:red;"><strong>Fehler:</strong> <?= $fehler_login ?></p>
                <?php endif; ?>
                
                <form method="post" action="index.php">
                    <label>Name:</label><br>
                    <input type="text" name="login_name"
                           value="<?= htmlspecialchars($_POST['login_name'] ?? '') ?>">
                    <br><br>

                    <label>Passwort:</label><br>
                    <input type="password" name="login_passwort">
                    <br><br>

                    <input type="submit" name="anmelden" value="Anmelden">
                </form>
            </td>
        </tr>
    </table>

    <br><hr><br>

<!-- === TEAM BEREICH === -->

    <!-- Tabelle damit beide Formulare nebeneinander stehen -->
    <table>
        <tr>
            <!-- LINKE SPALTE: Registrierung -->
            <td style="vertical-align:top; padding-right:50px;">
                <h2>Team registrieren</h2>
                <?php if ($reg_message): ?>
                    <p><strong><?= htmlspecialchars($reg_message) ?></strong></p>
                <?php endif; ?>
                <form action="index.php" method="post">
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

            <!-- RECHTE SPALTE: Login -->
            <td style="vertical-align:top; padding-left:50px;">
                <h2>Team anmelden</h2>
                <?php if ($login_error_message): ?>
                    <p style="color: red;"><?= htmlspecialchars($login_error_message) ?></p>
                <?php endif; ?>
                <form action="index.php" method="post">
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
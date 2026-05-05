<?php
// Autor: Julian Ploch
// Beschreibung: Startseite mit Registrierung und Login für Teams, Veranstalter und Sponsoren

ob_start();
session_start();
require_once __DIR__ . '/process.php';
include 'db.inc.php';

// Variablen initialisieren damit sie im HTML immer verfügbar sind
$fehler_reg   = '';
$erfolg_reg   = '';
$fehler_login = '';
$erfolg_login = '';

header("Access-Control-Allow-Origin: https://dbsnk.kirchbergnet.de");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $form_action = isset($_POST['form_action']) ? $_POST['form_action'] : '';
    switch($form_action) {
        case 'team_register':
            include 'teamlogin.php';
            break;
        case 'organizer_register':
            include 'RegVeran.php';
            break;
        case 'organizer_login':
            include 'LoginVeran.php';
            break;
        case 'sponsor_register':
            include 'register_sponsor_logik.inc.php';
            break;
        case 'sponsor_login':
            include 'login_sponsor_logik.inc.php';
            break;
        default:
            break;
    }
} else {
    // GET: damit Erfolgsmeldungen nach Redirect angezeigt werden
    include 'register_sponsor_logik.inc.php';
    include 'login_sponsor_logik.inc.php';
}
?>

<!DOCTYPE html>
<html lang="de">
<body>
    <h1>Willkommen, bitte wählen sie eine der folgenden Optionen:</h1>
    <hr>

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
                <h2>Teamchef anmelden</h2>
                <?php if ($login_error_message): ?>
                    <p style="color: red;"><?= htmlspecialchars($login_error_message) ?></p>
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

        <tr>
            <td colspan="3"><hr></td>
        </tr>

        <tr>
            <td style="vertical-align:top; padding-right:50px;">
                <h2>Rennveranstalter registrieren</h2>
                <form method="post" action="RegVeran.php">
                    <input type="hidden" name="action" value="organizer_register">
                    <label>Name: <input type="text" name="organizer_name" required></label><br><br>
                    <label>Passwort: <input type="password" name="password" required></label><br><br>
                    <input type="submit" value="Registrieren">
                </form>
            </td>

            <!-- TRENNLINIE -->
            <td style="border-left: 1px solid black; padding-right:50px;"></td>

            <td style="vertical-align:top; padding-left:50px;">
                <h2>Rennveranstalter anmelden</h2>
                <form method="post" action="LoginVeran.php">
                    <input type="hidden" name="action" value="organizer_login">
                    <label>Name: <input type="text" name="organizer_name" required></label><br><br>
                    <label>Passwort: <input type="password" name="password" required></label><br><br>
                    <input type="submit" value="Anmelden">
                </form>
            </td>
        </tr>

        <tr>
            <td colspan="3"><hr></td>
        </tr>

        <tr>
            <td style="vertical-align:top; padding-right:50px;">
                <h2>Sponsor registrieren</h2>

                <?php if (!empty($fehler_reg)): ?>
                    <p style="color:red;"><strong>Fehler:</strong> <?= $fehler_reg ?></p>
                <?php endif; ?>

                <?php if (!empty($erfolg_reg)): ?>
                    <p style="color:green;"><strong><?= $erfolg_reg ?></strong></p>
                <?php else: ?>
                <form method="post" action="index.php">
                    <input type="hidden" name="form_action" value="sponsor_register">

                    <label>Name:</label><br>
                    <input type="text" name="name" required>
                    <br><br>

                    <label>Passwort:</label><br>
                    <input type="password" name="passwort" required>
                    <br><br>

                    <label>Passwort wiederholen:</label><br>
                    <input type="password" name="passwort2" required>
                    <br><br>

                    <label>Budget (€):</label><br>
                    <input type="number" name="budget" min="0.01" step="0.01" required>
                    <br><br>

                    <input type="submit" name="registrieren" value="Registrieren">
                </form>
                <?php endif; ?>
            </td>

            <!-- TRENNLINIE -->
            <td style="border-left: 1px solid black; padding-right:50px;"></td>

            <td style="vertical-align:top; padding-left:50px;">
                <h2>Sponsor anmelden</h2>

                <?php if (!empty($fehler_login)): ?>
                    <p style="color:red;"><strong>Fehler:</strong> <?= $fehler_login ?></p>
                <?php endif; ?>

                <?php if (!empty($erfolg_login)): ?>
                    <p style="color:green;"><strong><?= $erfolg_login ?></strong></p>
                    <a href="dashboard_sponsor.php">Zum Sponsor-Bereich</a>
                <?php else: ?>
                <form method="post" action="index.php">
                    <input type="hidden" name="form_action" value="sponsor_login">

                    <label>Name:</label><br>
                    <input type="text" name="login_name" required>
                    <br><br>

                    <label>Passwort:</label><br>
                    <input type="password" name="login_passwort" required>
                    <br><br>

                    <input type="submit" name="anmelden" value="Anmelden">
                </form>
                <?php endif; ?>
            </td>
        </tr>
    </table>

</body>
</html>
<?php ob_end_flush(); ?>
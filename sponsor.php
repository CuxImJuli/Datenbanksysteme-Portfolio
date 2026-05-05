<?php
// Autor: Julian Ploch
// Beschreibung: Sponsor Bereich, für Login und Registrierung

session_start();
include 'db.inc.php';
include 'register_sponsor_logik.inc.php';
include 'login_sponsor_logik.inc.php';
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Sponsor</title>
</head>
<body>

    <h1>Sponsor Bereich</h1>
    <a href="index.php">Zurück zur Startseite</a>
    <hr>

    <table>
        <tr>

            <td style="vertical-align:top; padding-right:50px;">
                <h2>Sponsor registrieren</h2>

                <?php if (!empty($fehler_reg)): ?>
                    <p style="color:red;"><strong>Fehler:</strong> <?= $fehler_reg ?></p>
                <?php endif; ?>

                <?php if (!empty($erfolg_reg)): ?>
                    <p style="color:green;"><strong><?= $erfolg_reg ?></strong></p>
                <?php else: ?>
                <form method="post" action="sponsor.php">
                    <input type="hidden" name="action" value="sponsor_register">

                    <label>Name:</label><br>
                    <input type="text" name="name" required>
                    <br><br>

                    <label>Passwort:</label><br>
                    <input type="password" name="passwort" required>
                    <br><br>

                    <label>Passwort wiederholen:</label><br>
                    <input type="password" name="passwort2" required>
                    <br><br>

                    <!-- Budget-Feld war hier vergessen -->
                    <label>Budget (€):</label><br>
                    <input type="number" name="budget" min="0.01" step="0.01" required>
                    <br><br>

                    <input type="submit" name="registrieren" value="Registrieren">
                </form>
                <?php endif; ?>
            </td>

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
                <form method="post" action="sponsor.php">
                    <input type="hidden" name="action" value="sponsor_login">

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
<?php
session_start();
include 'db.inc.php';
include 'register_sponsor.php';
include 'login_sponsor.php';
require_once __DIR__ . '/process.php';

header("Access-Control-Allow-Origin: https://dbsnk.kirchbergnet.de");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
?>
<!-- -------------------------------------------------------- -->

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

                <?php if (isset($fehler_reg) && $fehler_reg !== ""): ?>
                    <p style="color:red;"><strong>Fehler:</strong> <?= $fehler_reg ?></p>
                <?php endif; ?>

                <?php if (isset($erfolg_reg) && $erfolg_reg !== ""): ?>
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

</body>
</html>
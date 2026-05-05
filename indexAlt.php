<?php

include 'db.inc.php';
include 'register_sponsor_logik.inc.php';
include 'login_sponsor_logik.inc.php';
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

           
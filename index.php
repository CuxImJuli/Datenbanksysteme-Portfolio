<?php
include 'db.inc.php';
include 'register_sponsor.php';
include 'login_sponsor.php';
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

    <h2>Sponsor registrieren</h2>

    <?php if ($fehler_reg !== ""): ?>
        <p style="color:red;"><strong>Fehler:</strong> <?= $fehler_reg ?></p>
    <?php endif; ?>

    <?php if ($erfolg_reg !== ""): ?>
        <p style="color:green;"><strong><?= $erfolg_reg ?></strong></p>
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

    <hr>

    <h2>Sponsor anmelden</h2>

    <?php if ($fehler_login !== ""): ?>
        <p style="color:red;"><strong>Fehler:</strong> <?= $fehler_login ?></p>
    <?php endif; ?>

    <?php if ($erfolg_login !== ""): ?>
        <p style="color:green;"><strong><?= $erfolg_login ?></strong></p>
        <a href="dashboard_sponsor.php">Zum Sponsor-Bereich</a>
    <?php else: ?>
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
    <?php endif; ?>

</body>
</html>
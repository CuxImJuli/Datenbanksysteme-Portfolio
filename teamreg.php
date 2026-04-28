<?php
require_once __DIR__ . '/process.php';

header("Access-Control-Allow-Origin: https://dbsnk.kirchbergnet.de");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $pdo = connectToDatabase();

        if (checkTeamExists($pdo, $_POST['teamname'])) {
            $message = "Team existiert bereits.";
        } else {
            registerUser($pdo, 'team', [
                'loginname' => $_POST['loginname'],
                'fname'     => $_POST['fname'],
                'lname'     => $_POST['lname'],
                'password'  => $_POST['password'],
                'teamname'  => $_POST['teamname'],
            ]);

            $message = "Ihr Team wurde erfolgreich angelegt!";
        }
    } catch (PDOException $e) {
        $message = "Fehler: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Team Registrierung</title>
</head>
<body>
    <h1>Teamregistrierung</h1>
    
    <?php if ($message): ?>
        <p><strong><?= htmlspecialchars($message) ?></strong></p>
    <?php endif; ?>

    <form action="teamreg.php" method="post">
        <label for="fname">Teamchef Vorname:</label><br>
        <input type="text" id="fname" name="fname" required><br>
        <label for="lname">Teamchef Nachname:</label><br>
        <input type="text" id="lname" name="lname" required><br>
        <label for="teamname">Teamname:</label><br>
        <input type="text" id="teamname" name="teamname" required><br>
        <label for="loginname">Loginname:</label><br>
        <input type="text" id="loginname" name="loginname" required><br>
        <label for="password">Passwort:</label><br>
        <input type="password" id="password" name="password" required><br>
        <br>
        <input type="submit" value="Registrieren">
    </form>
</body>
</html>

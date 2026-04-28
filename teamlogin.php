<?php
session_start();
require_once __DIR__ . '/process.php';

header("Access-Control-Allow-Origin: https://dbsnk.kirchbergnet.de");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginname = trim($_POST['loginname'] ?? '');
    $password  = $_POST['password'] ?? '';

    try {
        $pdo = connectToDatabase();

        if (validatePasswort($loginname, $password, $pdo)) {
            $_SESSION['loginname'] = $loginname;
            header("Location: TeamchefMenu.php");
            exit;
        } else {
            $error_message = "Login fehlgeschlagen. Bitte erneut versuchen.";
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        $error_message = "Ein Systemfehler ist aufgetreten.";
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Login</title>
</head>
<body>
    <h1>Team Login</h1>
    <?php if ($error_message): ?>
        <p style="color: red;"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>
    <form action="teamlogin.php" method="post">
        <label for="loginname">Loginname:</label><br>
        <input type="text" id="loginname" name="loginname" required><br><br>
        <label for="password">Passwort:</label><br>
        <input type="password" id="password" name="password" required><br><br>
        <input type="submit" value="Einloggen">
    </form>
</body>
</html>

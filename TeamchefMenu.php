<?php
session_start();
require_once __DIR__ . '/process.php';

if (empty($_SESSION['loginname'])) {
    header("Location: index.php");
    exit;
}

$loginname = $_SESSION['loginname'];

try {
    $pdo = connectToDatabase();

    $stmt = $pdo->prepare("SELECT Teamname FROM Team WHERE Loginname = :loginname LIMIT 1");
    $stmt->execute([':loginname' => $loginname]);
    $teamname = $stmt->fetchColumn();

    if (!$teamname) {
        die("Kein Team für diesen Teamchef gefunden.");
    }

    $stmt = $pdo->prepare(
        "SELECT `Mitarbeiter_ID`, Teamname FROM Fahrer WHERE Teamname = :teamname ORDER BY `Mitarbeiter_ID`"
    );
    $stmt->execute([':teamname' => $teamname]);
    $fahrer = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT Zielname FROM Trainingsziel ORDER BY Zielname");
    $stmt->execute();
    $ziele = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Datenbankfehler: " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Teamchef Menü</title>
</head>
<body>
    <h1>Teamchef Menü</h1>
    <p>Eingeloggt als: <?= htmlspecialchars($loginname) ?> | Team: <?= htmlspecialchars($teamname) ?></p>

    <hr>

    <?php if (isset($_GET['status'])): ?>
        <?php if ($_GET['status'] === 'ok'): ?>
            <p>Training erfolgreich eingetragen.</p>
        <?php else: ?>
            <p>Fehler beim Eintragen des Trainings.</p>
        <?php endif; ?>
    <?php endif; ?>

    <h2>Training eintragen</h2>

    <?php if (empty($fahrer)): ?>
        <p>Keine Fahrer im Team vorhanden.</p>
    <?php else: ?>
    <form action="training_save.php" method="post">
        <label for="datum">Datum:</label><br>
        <input type="date" id="datum" name="datum" required><br><br>

        <label for="kilometer">Kilometer:</label><br>
        <input type="number" id="kilometer" name="kilometer" step="0.01" min="0" required><br><br>

        <label for="mitarbeiterID">Mitarbeiter-ID:</label><br>
        <select id="mitarbeiterID" name="mitarbeiterID" required>
            <option value="" disabled selected>– wählen –</option>
            <?php foreach ($fahrer as $f): ?>
                <option value="<?= htmlspecialchars($f['Mitarbeiter-ID']) ?>">
                    <?= htmlspecialchars($f['Mitarbeiter-ID']) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Teamname:</label><br>
        <span><?= htmlspecialchars($teamname) ?></span>
        <input type="hidden" name="teamname" value="<?= htmlspecialchars($teamname) ?>"><br><br>

        <label for="zielname">Trainingsziel:</label><br>
        <select id="zielname" name="zielname" required>
            <option value="" disabled selected>– wählen –</option>
            <?php foreach ($ziele as $z): ?>
                <option value="<?= htmlspecialchars($z['Zielname']) ?>">
                    <?= htmlspecialchars($z['Zielname']) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <input type="submit" value="Speichern">
    </form>
    <?php endif; ?>
</body>
</html>

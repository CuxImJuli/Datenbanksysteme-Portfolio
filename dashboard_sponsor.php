<?php
// Autor: Julian Ploch
// Beschreibung: Dashboard für den Sponosr nach dem Login

ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

require_once __DIR__ . '/process.php';

if (!isset($_SESSION['rolle']) || $_SESSION['rolle'] !== 'sponsor') {
    header("Location: sponsor.php");
    exit();
}

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: sponsor.php");
    exit();
}

$dbConnection = connectToDatabase();
$sponsorID    = $_SESSION['id'];

function getZukuenftigeRennen(PDO $db): array {
    return $db->query("SELECT * FROM v_ZukuenftigeRennen")->fetchAll(PDO::FETCH_ASSOC);
}

function getSponsorBudget(PDO $db, int $sponsorID): array {
    $stmt = $db->prepare("SELECT Budget, RestBudget FROM Sponsor WHERE SponsorID = :id");
    $stmt->execute([':id' => $sponsorID]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['Budget' => 0, 'RestBudget' => 0];
}

function getSponsorBudgetVerteilung(PDO $db, int $sponsorID): array {
    $stmt = $db->prepare("SELECT * FROM v_SponsorBudgetUebersicht WHERE SponsorID = :id ORDER BY Datum ASC");
    $stmt->execute([':id' => $sponsorID]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getBereitsGesponsorteRennen(PDO $db, int $sponsorID): array {
    $stmt = $db->prepare("SELECT RennID FROM SponsorBudget WHERE SponsorID = :sid");
    $stmt->execute([':sid' => $sponsorID]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function budgetZuweisen(PDO $db, int $sponsorID, int $rennID, float $betrag): string {
   
    $stmt = $db->prepare("SELECT COUNT(*) FROM SponsorBudget WHERE SponsorID = :sid AND RennID = :rid");
    $stmt->execute([':sid' => $sponsorID, ':rid' => $rennID]);
    if ($stmt->fetchColumn() > 0) {
        return "Fehler: Sie haben diesem Rennen bereits ein Budget zugewiesen.";
    }

    $budget = getSponsorBudget($db, $sponsorID);
    if ($betrag > $budget['RestBudget']) {
        return "Fehler: Betrag übersteigt Ihr Restbudget von " . number_format($budget['RestBudget'], 2) . " €.";
    }

    $stmt = $db->prepare("INSERT INTO SponsorBudget (SponsorID, RennID, Betrag) VALUES (:sid, :rid, :betrag)");
    $stmt->execute([':sid' => $sponsorID, ':rid' => $rennID, ':betrag' => $betrag]);
   
    return "ok";
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['renn_id'], $_POST['betrag'])) {
    $rennID = (int)$_POST['renn_id'];
    $betrag = (float)str_replace(',', '.', $_POST['betrag']);

    if ($betrag <= 0) {
        $_SESSION['fehler'] = "Bitte einen gültigen Betrag eingeben.";
    } else {
        $ergebnis = budgetZuweisen($dbConnection, $sponsorID, $rennID, $betrag);
        if ($ergebnis === 'ok') {
            $_SESSION['erfolg'] = "Budget erfolgreich zugewiesen!";
        } else {
            $_SESSION['fehler'] = $ergebnis;
        }
    }
    
    header("Location: dashboard_sponsor.php");
    exit();
}


$fehler = $_SESSION['fehler'] ?? '';
$erfolg = $_SESSION['erfolg'] ?? '';
unset($_SESSION['fehler'], $_SESSION['erfolg']);

$rennen            = getZukuenftigeRennen($dbConnection);
$budget            = getSponsorBudget($dbConnection, $sponsorID);
$verteilung        = getSponsorBudgetVerteilung($dbConnection, $sponsorID);
$bereitsGesponsert = getBereitsGesponsorteRennen($dbConnection, $sponsorID);

$verfuegbareRennen = array_filter($rennen, function($r) use ($bereitsGesponsert) {
    return !in_array($r['ID'], $bereitsGesponsert);
});
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Sponsor Dashboard</title>
</head>
<body>

<h1>Willkommen, <?= htmlspecialchars($_SESSION['name']) ?>!</h1>
<p>Sie sind erfolgreich als Sponsor angemeldet.</p>

<p>
    <strong>Gesamtbudget:</strong> <?= number_format($budget['Budget'], 2) ?> € &nbsp;|&nbsp;
    <strong>Restbudget:</strong> <?= number_format($budget['RestBudget'], 2) ?> €
</p>
<hr>

<nav><a href="index.php">Zur Startseite</a></nav>
<hr>

<h2>Budget einem Rennen zuweisen</h2>

<?php if ($fehler): ?>
    <p style="color:red;"><?= htmlspecialchars($fehler) ?></p>
<?php endif; ?>
<?php if ($erfolg): ?>
    <p style="color:green;"><?= htmlspecialchars($erfolg) ?></p>
<?php endif; ?>

<?php if (empty($verfuegbareRennen)): ?>
    <p>Sie haben bereits allen verfügbaren Rennen ein Budget zugewiesen.</p>
<?php else: ?>
    <form method="post" action="dashboard_sponsor.php">
        <label>Rennen auswählen:
            <select name="renn_id">
                <?php foreach ($verfuegbareRennen as $r): ?>
                    <option value="<?= htmlspecialchars($r['ID']) ?>">
                        <?= htmlspecialchars($r['Name']) ?> –
                        <?= htmlspecialchars($r['Datum']) ?> –
                        <?= htmlspecialchars($r['Startort']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <br><br>
        <label>Betrag (€):
            <input type="number" name="betrag" min="0.01" step="0.01" required>
        </label>
        <br><br>
        <input type="submit" value="Budget zuweisen">
    </form>
<?php endif; ?>

<hr>

<h2>Zukünftige Rennen</h2>

<?php if (empty($rennen)): ?>
    <p>Aktuell sind keine zukünftigen Rennen geplant.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Datum</th>
                <th>Startort</th>
                <th>Kilometer</th>
                <th>Höhenmeter</th>
                <th>Max. Steigung (%)</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($rennen as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['ID']) ?></td>
                <td><?= htmlspecialchars($r['Name']) ?></td>
                <td><?= htmlspecialchars($r['Datum']) ?></td>
                <td><?= htmlspecialchars($r['Startort']) ?></td>
                <td><?= htmlspecialchars($r['AnzahlKM']) ?></td>
                <td><?= htmlspecialchars($r['Höhenmeter']) ?></td>
                <td><?= htmlspecialchars($r['MaxSteigung']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<hr>

<h2>Meine Budgetverteilung</h2>

<?php if (empty($verteilung)): ?>
    <p>Sie haben noch keinem Rennen Budget zugewiesen.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>Rennen</th>
                <th>Datum</th>
                <th>Startort</th>
                <th>Zugewiesener Betrag (€)</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($verteilung as $v): ?>
            <tr>
                <td><?= htmlspecialchars($v['RennName']) ?></td>
                <td><?= htmlspecialchars($v['Datum']) ?></td>
                <td><?= htmlspecialchars($v['Startort']) ?></td>
                <td><?= number_format($v['ZugewiesenerBetrag'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<br>
<form method="post" action="dashboard_sponsor.php">
    <input type="submit" name="logout" value="Abmelden">
</form>

</body>
</html>
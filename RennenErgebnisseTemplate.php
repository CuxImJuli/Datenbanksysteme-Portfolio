<!DOCTYPE html>
<html lang="de">

<!-- Autor: Linda Maaß -->

<head>
    <meta charset="UTF-8">
    <title>Ergebnisse</title>
</head>
<body>

<h1>Ergebnisse erfassen</h1>


<!-- Rennen auswählen -->
<form method="get" action="RennenErgebnisse.php">
    Rennen-ID:
    <input type="number" name="race_id" min="1" step="1" required>
    <input type="submit" value="ok">
</form>

<hr>

<?php if ($error !== ''): ?>
    <p><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
<?php if ($success !== ''): ?>
    <p><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<?php if ($raceId > 0 && !$race): ?>
    <p>Rennen mit ID <?= (int) $raceId ?> nicht gefunden.</p>
<?php elseif ($race && $participant === []): ?>
    <p>Rennen gefunden, aber es wurden noch keine Teilnehmer angemeldet.</p>
<?php elseif ($race): ?>

<?php if ($locked): ?>
    <p>Ergebnisse sind bereits vollstaendig erfasst.</p>
<?php endif; ?>

<!-- Formular zum Speichern der Ergebnisse -->
<form method="post" action="RennenErgebnisse.php">
<input type="hidden" name="race_id" value="<?= (int) $raceId ?>">

<table>
<tr>
    <th>StartNr</th>
    <th>Nachname</th>
    <th>Vorname</th>
    <th>Platz</th>
    <th>Fahrtzeit</th>
</tr>

<?php foreach ($participant as $t): ?>
<tr>
    <td><?= htmlspecialchars((string) $t['StartNr']) ?></td>
    <td><?= htmlspecialchars($t['Nachname']) ?></td>
    <td><?= htmlspecialchars($t['Vorname']) ?></td>

    <td>
        <?php if ($locked): ?>
            <?= htmlspecialchars((string) $t['Platzierung']) ?>
        <?php else: ?>
            <input type="number" min="1" name="platzierung[<?= (int) $t['Mitarbeiter_ID'] ?>]" required>
        <?php endif; ?>
    </td>

    <td>
        <?php if ($locked): ?>
            <?= htmlspecialchars((string) $t['Fahrtzeit']) ?>
        <?php else: ?>
            <input
                type="text"
                name="fahrtzeit[<?= (int) $t['Mitarbeiter_ID'] ?>]"
                placeholder="hh:mm:ss"
                pattern="\d{2}:[0-5]\d:[0-5]\d"
                title="Bitte im Format hh:mm:ss eingeben, z. B. 01:23:45"
                required
            >
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>

</table>

<?php if (!$locked): ?>
<input type="submit" value="Speichern">
<?php endif; ?>

</form>

<?php endif; ?>

<p><a href="RennenAnlegen.html#ergebnisse">Zurück zur Rennverwaltung</a></p>

</body>
</html>
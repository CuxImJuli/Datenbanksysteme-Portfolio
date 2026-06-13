<?php
// Datei: rennanmeldung.inc.php
// author: Juri Schröder
//Funktionen für das Rennanmeldungs- und kopierenmenü
//Teamname ermitteln
function getTeamnameByLogin(PDO $pdo, string $loginname) {
    $stmt = $pdo->prepare("SELECT Teamname FROM Team WHERE Loginname = :loginname LIMIT 1");
    $stmt->execute([':loginname' => $loginname]);
    return $stmt->fetchColumn();
}
//View für zukünftige Rennen aufrufen
function getZukuenftigeRennen(PDO $pdo): array {
    $stmt = $pdo->prepare("SELECT ID, Name, Datum, Startort FROM v_ZukuenftigeRennen ORDER BY Datum ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
//Fahrer aus Team
function getTeamFahrer(PDO $pdo, string $teamname): array {
    $stmt = $pdo->prepare("SELECT Mitarbeiter_ID, Vorname, Nachname FROM Fahrer WHERE Teamname = :teamname ORDER BY Nachname ASC");
    $stmt->execute([':teamname' => $teamname]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
//Funktion zum rennen kopieren, ruft stored procedure auf
function rennenKopieren(PDO $pdo, int $quell_id, int $ziel_id, string $teamname) {
    try {
        $stmtSP = $pdo->prepare("CALL sp_rennen_kopieren(:alt, :neu, :team)");
        $stmtSP->execute([
            ':alt'  => $quell_id,
            ':neu'  => $ziel_id,
            ':team' => $teamname
        ]);
        return true;
    } catch (PDOException $e) {
        return "Fehler beim Kopieren: " . htmlspecialchars($e->getMessage());
    }
}
//zählt Fahrer in Rennen
function countDriversInRace(PDO $pdo, int $quell_id, string $teamname): int {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM Teilnahme t
        INNER JOIN Fahrer f ON t.Mitarbeiter_ID = f.Mitarbeiter_ID
        WHERE t.Rennen_ID = :quell_id 
          AND t.Teamname = :teamname1 
          AND f.Teamname = :teamname2
    ");
    $stmt->execute([
        ':quell_id'  => $quell_id,
        ':teamname1' => $teamname,
        ':teamname2' => $teamname
    ]);
    return intval($stmt->fetchColumn());
}
//alle Rennen mit angemeldeten Fahrern des eigenen Teams
function getQuellRennenListe(PDO $pdo, string $teamname): array {
    $stmt = $pdo->prepare("
        SELECT DISTINCT r.ID, r.Name, r.Datum 
        FROM Rennen r
        INNER JOIN Teilnahme t ON r.ID = t.Rennen_ID
        WHERE t.Teamname = :teamname
        ORDER BY r.Datum DESC
    ");
    $stmt->execute([':teamname' => $teamname]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
//Rennnamen ermitteln
function getRennenName(PDO $pdo, int $rennen_id): string {
    $stmt = $pdo->prepare("SELECT Name FROM Rennen WHERE ID = :qid");
    $stmt->execute([':qid' => $rennen_id]);
    return (string)$stmt->fetchColumn(); 
}
//Zukünftige Rennen, Quellrennen raus genommen
function getZielRennenListe(PDO $pdo, int $quell_id): array {
    $stmt = $pdo->prepare("
        SELECT z.ID, z.Name, z.Datum, z.Startort 
        FROM v_ZukuenftigeRennen z
        WHERE z.ID != :quell_id
        ORDER BY z.Datum ASC
    ");
    $stmt->execute([':quell_id' => $quell_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
//gibt Fahrer des ausgewählten Rennens aus
function getFahrerInRennen(PDO $pdo, int $quell_id, string $teamname): array {
    $stmt = $pdo->prepare("
        SELECT f.Mitarbeiter_ID, f.Nachname, f.Vorname 
        FROM Teilnahme t
        INNER JOIN Fahrer f ON f.Mitarbeiter_ID = t.Mitarbeiter_ID
        WHERE t.Rennen_ID = :quell_id 
          AND t.Teamname = :teamname1 
          AND f.Teamname = :teamname2
        ORDER BY f.Nachname ASC
    ");
    $stmt->execute([
        ':quell_id'  => $quell_id,
        ':teamname1' => $teamname,
        ':teamname2' => $teamname
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
//prüfung ob fahrer schon zum Rennen angemeldet ist
function isFahrerBereitsAngemeldet(PDO $pdo, int $m_id, int $rennen_id): bool {
    $stmt = $pdo->prepare("SELECT Mitarbeiter_ID FROM Teilnahme WHERE Mitarbeiter_ID = :m_id AND Rennen_ID = :rennen_id");
    $stmt->execute([
        ':m_id'      => $m_id,
        ':rennen_id' => $rennen_id
    ]);
    return (bool)$stmt->fetch();
}
//Fahrer im Rennen eintragen
function registriereFahrerInRennen(PDO $pdo, int $m_id, string $teamname, int $rennen_id): bool {
    $stmt = $pdo->prepare("
        INSERT INTO Teilnahme (Mitarbeiter_ID, Teamname, Rennen_ID, StartNr, PrämieTeam, PrämieVeranstalter) 
        VALUES (:m_id, :team, :rennen_id, Null, 0.00, 0.00)
    ");
    return $stmt->execute([
        ':m_id'      => $m_id,
        ':team'      => $teamname,
        ':rennen_id' => $rennen_id
    ]);
}
?>
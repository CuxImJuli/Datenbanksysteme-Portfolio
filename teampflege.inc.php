<?php
// author: Juri Schröder
//Funktionen für die teampflege.php

//Holt den Teamnamen des angemeldeten Benutzers
function getTeamnameByLogin(PDO $pdo, string $loginname) {
    $stmt = $pdo->prepare("SELECT Teamname FROM Team WHERE Loginname = :loginname LIMIT 1");
    $stmt->execute([':loginname' => $loginname]);
    return $stmt->fetchColumn();
}
//Lädt die Daten eines einzelnen Fahrers, wenn "Bearbeiten" geklickt wurde
function getFahrerEditData(PDO $pdo, int $m_id, string $teamname) {

    $stmtEdit = $pdo->prepare("SELECT * FROM Fahrer WHERE Mitarbeiter_ID = :id AND Teamname = :teamname");
    $stmtEdit->execute([
        ':id'       => $m_id, 
        ':teamname' => $teamname
    ]);
    return $stmtEdit->fetch(PDO::FETCH_ASSOC);
}
//Löscht einen Fahrer aus der Datenbank
function deleteFahrer(PDO $pdo, int $delete_id, string $teamname): bool {
    $stmtDelete = $pdo->prepare("DELETE FROM Fahrer WHERE Mitarbeiter_ID = :id AND Teamname = :teamname");
    return $stmtDelete->execute([
        ':id'       => $delete_id,
        ':teamname' => $teamname
    ]);
}
//Ruft die Stored Procedure zum Speichern oder Ändern auf
function anlegenAnpassenFahrer(PDO $pdo, int $m_id, string $teamname, string $vorname, string $nachname, string $vorwahl, string $nummer, string $plz, string $ort, string $strasse, string $hausnr): bool {
    $stmtSP = $pdo->prepare("
        CALL sp_Fahrer_anlegen_anpassen(
            :id, :teamname, :vorname, :nachname, :vorwahl, 
            :nummer, :plz, :ort, :strasse, :hausnr
        )
    ");
    return $stmtSP->execute([
        ':id'       => $m_id,
        ':teamname' => $teamname,
        ':vorname'  => $vorname,
        ':nachname' => $nachname,
        ':vorwahl'  => $vorwahl,
        ':nummer'   => $nummer,
        ':plz'      => $plz,
        ':ort'      => $ort,
        ':strasse'  => $strasse,
        ':hausnr'   => $hausnr
    ]);
}
//Lädt alle Fahrer des Teams für die Übersichtstabelle
function getAlleFahrer(PDO $pdo, string $teamname): array {
    $stmtList = $pdo->prepare("SELECT * FROM Fahrer WHERE Teamname = :teamname ORDER BY Mitarbeiter_ID ASC");
    $stmtList->execute([':teamname' => $teamname]);
    return $stmtList->fetchAll(PDO::FETCH_ASSOC);
}
?>
<?php
// Autor: Julian Ploch
// Beschreibung: Funktionen

//Funktion zur Überpürfung von einer Registrierung
function sponsorExistiert($pdo, $name) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Sponsor WHERE Name = ?");
    $stmt->execute([$name]);
    $anzahl = $stmt->fetchColumn();
    return $anzahl > 0;
}
?>
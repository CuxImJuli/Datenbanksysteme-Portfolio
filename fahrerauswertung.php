<?php
/**
 * Author: Noah S. Kipp
 */
// Auswertungsklasse für Trainingskennzahlen
class TeamAuswertung {
    private PDO $pdo;
    private string $teamname, $zielname = 'Alle Ziele', $startdatum = '', $enddatum = '';
    private array $auswertung = [];

    public function __construct(PDO $pdo, string $team) {
        $this->pdo = $pdo; 
        $this->teamname = $team;
    }

    public function setZielname(string $z): void { $this->zielname = $z; }
    public function setZeitraum(string $s, string $e): void { $this->startdatum = $s; $this->enddatum = $e; }
    public function getAuswertung(): array { return $this->auswertung; }

    // Aufruf der Procedure für die Berechnung
    public function ermittleKennzahlen(): void {
        $stmt = $this->pdo->prepare("CALL p_getTeamAuswertung_nsk(:tname, :zname, :sdatum, :edatum)");
        $stmt->execute([
            ':tname'  => $this->teamname,
            ':zname'  => $this->zielname,
            ':sdatum' => $this->startdatum,
            ':edatum' => $this->enddatum
        ]);
        
        $this->auswertung = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

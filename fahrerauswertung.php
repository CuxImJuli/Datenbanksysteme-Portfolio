<?php
/**
 * Author: Noah S. Kipp
 */
// OOP-Auswertungsklasse für Trainingskennzahlen (Team-Ebene, reines PHP)
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

    public function ermittleKennzahlen(): void {
        // Abfrage der Trainingsdaten des Teams
        $q = "SELECT Mitarbeiter_ID, Datum, Kilometer FROM Training WHERE Teamname = :t";
        $p = [':t' => $this->teamname];
        
        if ($this->zielname !== 'Alle Ziele') { $q .= " AND Zielname = :z"; $p[':z'] = $this->zielname; }
        if (!empty($this->startdatum)) { $q .= " AND Datum >= :s"; $p[':s'] = $this->startdatum; }
        if (!empty($this->enddatum)) { $q .= " AND Datum <= :e"; $p[':e'] = $this->enddatum; }

        $stmt = $this->pdo->prepare($q);
        $stmt->execute($p);
        
        $gruppiert = [];
        
        // Daten im Array sortieren
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $fahrer = $row['Mitarbeiter_ID'];
            $monat = date('Y-m', strtotime($row['Datum']));
            $gruppiert[$fahrer][$monat][] = (float)$row['Kilometer'];
        }

        // Auswertung berechnen
        foreach ($gruppiert as $fahrer => $monate) {
            ksort($monate); // Monate sortieren
            
            foreach ($monate as $monat => $kms) {
                $c = count($kms);
                sort($kms); // Kilometer aufsteigend sortieren
                
                $sum = array_sum($kms);
                $avg = $sum / $c;
                
                // Median
                $m = (int)($c / 2);
                $median = ($c % 2 != 0) ? $kms[$m] : ($kms[$m - 1] + $kms[$m]) / 2;
                
                // Standardabweichung
                $varSum = 0;
                foreach ($kms as $km) {
                    $varSum += pow($km - $avg, 2);
                }
                $stddev = $c > 1 ? sqrt($varSum / ($c - 1)) : 0;

                // Fertige Zeile speichern
                $this->auswertung[] = [
                    'Mitarbeiter_ID' => $fahrer,
                    'Monat'          => $monat,
                    'count'          => $c,
                    'sum'            => $sum,
                    'avg'            => $avg,
                    'min'            => min($kms),
                    'max'            => max($kms),
                    'median'         => $median,
                    'stddev'         => $stddev
                ];
            }
        }
    }
}

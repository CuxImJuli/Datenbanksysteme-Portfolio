<?php
/**
 * Author: Noah S. Kipp
 */
require_once __DIR__ . '/process.php';

// Auswertungsklasse für Trainingskennzahlen
class TeamAuswertung
{
    private $teamname = '', $zielname = 'Alle Ziele', $startdatum = '', $enddatum = '';
    private $auswertung = [];

    public function setTeamname($teamname)
    {
        $this->teamname = $teamname;
    }

    public function setZielname($zielname)
    {
        $this->zielname = $zielname;
    }

    public function setZeitraum($startdatum, $enddatum)
    {
        $this->startdatum = $startdatum;
        $this->enddatum = $enddatum;
    }

    public function getAuswertung()
    {
        return $this->auswertung;
    }

    public function ermittleKennzahlen()
    {
        $pdo = connectToDatabase();
        $params = [':tname' => $this->teamname];

        // Kennzahlen pro Fahrer pro Monat
        $sql = "SELECT Mitarbeiter_ID, Kilometer, Datum FROM Training WHERE Teamname = :tname";

        // Filterparameter hinzufügen
        if ($this->zielname !== 'Alle Ziele') {
            $sql .= " AND Zielname = :ziel";
            $params[':ziel'] = $this->zielname;
        }

        if ($this->startdatum) {
            $sql .= " AND Datum >= :start";
            $params[':start'] = $this->startdatum;
        }

        if ($this->enddatum) {
            $sql .= " AND Datum <= :ende";
            $params[':ende'] = $this->enddatum;
        }

        $sql .= " ORDER BY Mitarbeiter_ID, Datum";

        // SQL-Abfrage ausführen
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $fahrerData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Daten in geschachtelte Array strukturieren
        foreach ($fahrerData as $fahrer) {
            $mitarbeiterId = $fahrer['Mitarbeiter_ID'];
            $monat = date('Y-m', strtotime($fahrer['Datum']));
            $this->auswertung[$mitarbeiterId][$monat][] = [
                'Mitarbeiter_ID' => $mitarbeiterId,
                'Datum' => $fahrer['Datum'],
                'Kilometer' => $fahrer['Kilometer'],
            ];
        }

        // Kennzahlen berechnen
        foreach ($this->auswertung as $fahrerId => $monate) {
            foreach ($monate as $monat => $trainings) {
                $kmProMonat = array_column($trainings, 'Kilometer');
                $this->auswertung[$fahrerId][$monat] = [
                    'Minimum' => min($kmProMonat),
                    'Maximum' => max($kmProMonat),
                    'Anzahl' => count($trainings),
                    'Summe' => array_sum($kmProMonat),
                    'Durchschnitt' => array_sum($kmProMonat) / count($kmProMonat),
                    'Median' => $this->berechneMedian($kmProMonat),
                    'Standardabweichung' => $this->berechneStandartabweichung($kmProMonat),
                ];
            }
        }
    }

    // Median berechnen
    private function berechneMedian($kmProMonat)
    {
        if (empty($kmProMonat)) {
            return 0.0;
        }
        sort($kmProMonat);
        $anzahl = count($kmProMonat);
        $mitte = floor($anzahl / 2);

        if ($anzahl % 2 == 1) {
            return (float) $kmProMonat[$mitte];
        }
        return (float) ($kmProMonat[$mitte - 1] + $kmProMonat[$mitte]) / 2.0;
    }

    // Standardabweichung berechnen
    private function berechneStandartabweichung($kmProMonat)
    {
        if (empty($kmProMonat)) {
            return 0.0;
        }
        $durchschnitt = array_sum($kmProMonat) / count($kmProMonat);
        $sumAbweichungenSq = 0;
        foreach ($kmProMonat as $km) {
            $sumAbweichungenSq += pow(($km - $durchschnitt), 2);
        }
        $varianz = $sumAbweichungenSq / count($kmProMonat);
        return (float) sqrt($varianz);
    }
}

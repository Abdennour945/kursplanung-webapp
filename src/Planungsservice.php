<?php

class Planungsservice {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function istTrainerKrank($trainerId, $datum) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) 
            FROM krankmeldung
            WHERE trainer_id = ?
            AND status = 'aktiv'
            AND ? BETWEEN datum_von AND datum_bis
        ");
        $stmt->execute([$trainerId, $datum]);

        return $stmt->fetchColumn() > 0;
    }

    public function hatTrainerKursZurGleichenZeit($trainerId, $datum, $uhrzeit) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) 
            FROM kurs
            WHERE datum = ?
            AND uhrzeit = ?
            AND (trainer_id = ? OR ersatztrainer_id = ?)
        ");
        $stmt->execute([$datum, $uhrzeit, $trainerId, $trainerId]);

        return $stmt->fetchColumn() > 0;
    }

    public function istRaumVerfuegbar($raumId, $datum, $uhrzeit) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) 
            FROM kurs
            WHERE raum_id = ?
            AND datum = ?
            AND uhrzeit = ?
        ");
        $stmt->execute([$raumId, $datum, $uhrzeit]);

        return $stmt->fetchColumn() == 0;
    }

    public function zaehleKursstundenInWoche($trainerId, $datum) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*)
            FROM kurs
            WHERE (trainer_id = ? OR ersatztrainer_id = ?)
            AND datum BETWEEN 
                date_trunc('week', ?::date)::date 
                AND (date_trunc('week', ?::date) + interval '6 days')::date
        ");
        $stmt->execute([$trainerId, $trainerId, $datum, $datum]);

        return (int) $stmt->fetchColumn();
    }

    public function maxKursstunden($trainerId) {
        $stmt = $this->pdo->prepare("
            SELECT a.max_kursstunden
            FROM mitarbeiter m
            JOIN arbeitszeitmodell a 
            ON m.arbeitszeitmodell_id = a.arbeitszeitmodell_id
            WHERE m.mitarbeiter_id = ?
        ");
        $stmt->execute([$trainerId]);

        return (int) $stmt->fetchColumn();
    }

    public function hatGenugFahrzeit($trainerId, $datum, $uhrzeit, $raumId) {
        $stmt = $this->pdo->prepare("
            SELECT standort_id
            FROM raum
            WHERE raum_id = ?
        ");
        $stmt->execute([$raumId]);
        $neuerStandort = $stmt->fetchColumn();

        $stmt = $this->pdo->prepare("
            SELECT k.uhrzeit, r.standort_id
            FROM kurs k
            JOIN raum r ON k.raum_id = r.raum_id
            WHERE k.datum = ?
            AND (k.trainer_id = ? OR k.ersatztrainer_id = ?)
        ");
        $stmt->execute([$datum, $trainerId, $trainerId]);
        $kurse = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $neueZeit = strtotime($datum . " " . $uhrzeit);

        foreach ($kurse as $kurs) {
            $alteZeit = strtotime($datum . " " . $kurs["uhrzeit"]);

            if ($kurs["standort_id"] != $neuerStandort) {
                $diffStunden = abs($neueZeit - $alteZeit) / 3600;

                if ($diffStunden < 2) {
                    return false;
                }
            }
        }

        return true;
    }

    public function pruefeTrainer($trainerId, $datum, $uhrzeit, $raumId) {
        if ($this->istTrainerKrank($trainerId, $datum)) {
            return "Der Trainer ist an diesem Datum krank gemeldet.";
        }

        if ($this->hatTrainerKursZurGleichenZeit($trainerId, $datum, $uhrzeit)) {
            return "Der Trainer ist zu dieser Zeit bereits einem anderen Kurs zugeordnet.";
        }

        if (!$this->hatGenugFahrzeit($trainerId, $datum, $uhrzeit, $raumId)) {
            return "Wegen Standortwechsel ist nicht genug Fahrzeit vorhanden.";
        }

        $stunden = $this->zaehleKursstundenInWoche($trainerId, $datum);
        $max = $this->maxKursstunden($trainerId);

        if ($stunden >= $max) {
            return "Die maximale Kurszeit des Trainers wurde erreicht.";
        }

        return null;
    }

    public function sucheVertretung($kursId) {
        $stmt = $this->pdo->prepare("
            SELECT 
                k.*,
                r.standort_id AS kurs_standort_id
            FROM kurs k
            JOIN raum r ON k.raum_id = r.raum_id
            WHERE k.kurs_id = ?
        ");
        $stmt->execute([$kursId]);
        $kurs = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$kurs) {
            return null;
        }

        $stmt = $this->pdo->prepare("
            SELECT 
                mitarbeiter_id,
                name,
                rolle,
                standort_id
            FROM mitarbeiter
            WHERE rolle = 'Trainer'
            AND status = 'aktiv'
            AND standort_id = ?
            ORDER BY mitarbeiter_id
        ");
        $stmt->execute([$kurs["kurs_standort_id"]]);
        $trainerListe = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $kandidaten = [];

        foreach ($trainerListe as $trainer) {
            if ($trainer["mitarbeiter_id"] == $kurs["trainer_id"]) {
                continue;
            }

            $fehler = $this->pruefeTrainer(
                $trainer["mitarbeiter_id"],
                $kurs["datum"],
                $kurs["uhrzeit"],
                $kurs["raum_id"]
            );

            if ($fehler === null) {
                $stunden = $this->zaehleKursstundenInWoche(
                    $trainer["mitarbeiter_id"],
                    $kurs["datum"]
                );

                $trainer["kursstunden"] = $stunden;
                $kandidaten[] = $trainer;
            }
        }

        usort($kandidaten, function($a, $b) {
            return $a["kursstunden"] <=> $b["kursstunden"];
        });

        return $kandidaten[0] ?? null;
    }

    public function trainerZuweisen($kursId, $ersatztrainerId) {
        $stmt = $this->pdo->prepare("
            UPDATE kurs
            SET ersatztrainer_id = ?, status = 'vertretung'
            WHERE kurs_id = ?
        ");

        return $stmt->execute([$ersatztrainerId, $kursId]);
    }
}
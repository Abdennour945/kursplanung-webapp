<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../includes/auth.php";
require_once "../config/db.php";

requireLogin();
requireRole(["Sekretariat", "Administrator"]);

if (!isset($_GET["id"])) {
    die("Keine Kurs-ID angegeben.");
}

$kursId = $_GET["id"];
$error = "";

$zeiten = [
    "08:00",
    "09:00",
    "10:00",
    "11:00",
    "12:00",
    "13:00",
    "14:00",
    "15:00",
    "16:00",
    "17:00",
    "18:00",
    "19:00"
];

$stmt = $pdo->prepare("SELECT * FROM kurs WHERE kurs_id = ?");
$stmt->execute([$kursId]);
$kurs = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$kurs) {
    die("Kurs nicht gefunden.");
}

$trainerListe = $pdo->query("
    SELECT 
        m.mitarbeiter_id,
        m.name,
        a.bezeichnung AS arbeitszeitmodell
    FROM mitarbeiter m
    JOIN arbeitszeitmodell a
    ON m.arbeitszeitmodell_id = a.arbeitszeitmodell_id
    WHERE m.rolle = 'Trainer'
    AND m.status = 'aktiv'
    ORDER BY m.mitarbeiter_id
")->fetchAll(PDO::FETCH_ASSOC);

$raeume = $pdo->query("
    SELECT 
        r.raum_id,
        r.name AS raum_name,
        r.nummer,
        s.name AS standort_name
    FROM raum r
    JOIN standort s ON r.standort_id = s.standort_id
    ORDER BY s.name, r.name
")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $datum = $_POST["datum"];
    $uhrzeit = $_POST["uhrzeit"];
    $raumId = $_POST["raum_id"];
    $trainerId = $_POST["trainer_id"];
    $status = $_POST["status"];

    if (empty($datum) || empty($uhrzeit) || empty($raumId) || empty($trainerId) || empty($status)) {
        $error = "Bitte alle Felder ausfüllen.";
    } else {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM kurs
            WHERE kurs_id <> ?
            AND raum_id = ?
            AND datum = ?
            AND uhrzeit = ?
        ");
        $stmt->execute([$kursId, $raumId, $datum, $uhrzeit]);
        $raumBelegt = $stmt->fetchColumn() > 0;

        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM kurs
            WHERE kurs_id <> ?
            AND datum = ?
            AND uhrzeit = ?
            AND (trainer_id = ? OR ersatztrainer_id = ?)
        ");
        $stmt->execute([$kursId, $datum, $uhrzeit, $trainerId, $trainerId]);
        $trainerBelegt = $stmt->fetchColumn() > 0;

        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM krankmeldung
            WHERE trainer_id = ?
            AND status = 'aktiv'
            AND ? BETWEEN datum_von AND datum_bis
        ");
        $stmt->execute([$trainerId, $datum]);
        $trainerKrank = $stmt->fetchColumn() > 0;

        if ($raumBelegt) {
            $error = "Der Raum ist zu dieser Zeit bereits belegt.";
        } elseif ($trainerBelegt) {
            $error = "Der Trainer ist zu dieser Zeit bereits einem anderen Kurs zugeordnet.";
        } elseif ($trainerKrank) {
            $error = "Der Trainer ist an diesem Datum krank gemeldet.";
        } else {
            $stmt = $pdo->prepare("
                UPDATE kurs
                SET datum = ?,
                    uhrzeit = ?,
                    dauer_minuten = 60,
                    raum_id = ?,
                    trainer_id = ?,
                    status = ?
                WHERE kurs_id = ?
            ");
            $stmt->execute([$datum, $uhrzeit, $raumId, $trainerId, $status, $kursId]);

            header("Location: kursplan.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Kurs bearbeiten</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<div class="container">
    <h1>Kurs bearbeiten</h1>

    <p><a href="kursplan.php">Zurück zum Kursplan</a></p>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Datum</label>
        <input 
            type="date" 
            name="datum" 
            value="<?= htmlspecialchars($_POST["datum"] ?? $kurs["datum"]) ?>" 
            required
        >

        <label>Uhrzeit</label>
        <select name="uhrzeit" required>
            <?php foreach ($zeiten as $zeit): ?>
                <?php
                    $ende = date("H:i", strtotime("2000-01-01 " . $zeit . " +1 hour"));
                    $aktuelleZeit = substr($_POST["uhrzeit"] ?? $kurs["uhrzeit"], 0, 5);
                    $selected = ($aktuelleZeit === $zeit) ? "selected" : "";
                ?>
                <option value="<?= $zeit ?>" <?= $selected ?>>
                    <?= $zeit ?> - <?= $ende ?>
                </option>
            <?php endforeach; ?>
        </select>

        <p><strong>Dauer:</strong> Jeder Kurs dauert 60 Minuten.</p>

        <label>Raum / Standort</label>
        <select name="raum_id" required>
            <?php foreach ($raeume as $raum): ?>
                <?php
                    $selected = (($_POST["raum_id"] ?? $kurs["raum_id"]) == $raum["raum_id"]) ? "selected" : "";
                ?>
                <option value="<?= $raum["raum_id"] ?>" <?= $selected ?>>
                    <?= htmlspecialchars($raum["standort_name"] . " - " . $raum["raum_name"] . " (" . $raum["nummer"] . ")") ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Trainer</label>
        <select name="trainer_id" required>
            <?php foreach ($trainerListe as $trainer): ?>
                <?php
                    $selected = (($_POST["trainer_id"] ?? $kurs["trainer_id"]) == $trainer["mitarbeiter_id"]) ? "selected" : "";
                ?>
                <option value="<?= $trainer["mitarbeiter_id"] ?>" <?= $selected ?>>
                    <?= htmlspecialchars($trainer["name"] . " (" . $trainer["arbeitszeitmodell"] . ")") ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Status</label>
        <select name="status" required>
            <?php
                $statusListe = ["normal", "vertretung_noetig", "vertretung"];
                $aktuellerStatus = $_POST["status"] ?? $kurs["status"];
            ?>

            <?php foreach ($statusListe as $status): ?>
                <option value="<?= $status ?>" <?= ($aktuellerStatus === $status) ? "selected" : "" ?>>
                    <?= htmlspecialchars($status) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Änderungen speichern</button>
    </form>
</div>

</body>
</html>
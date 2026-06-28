<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../includes/auth.php";
require_once "../config/db.php";
require_once "../src/Planungsservice.php";

requireLogin();
requireRole(["Trainer", "Sekretariat"]);

$service = new Planungsservice($pdo);

$error = "";
$success = "";
$details = [];

$mitarbeiterId = $_SESSION["user_id"];
$mitarbeiterName = $_SESSION["name"];
$rolle = $_SESSION["rolle"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $von = $_POST["datum_von"];
    $bis = $_POST["datum_bis"];

    if (empty($von) || empty($bis)) {
        $error = "Bitte alle Felder ausfüllen.";
    } elseif ($von > $bis) {
        $error = "Datum von darf nicht nach Datum bis liegen.";
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO krankmeldung (trainer_id, datum_von, datum_bis, status)
                VALUES (?, ?, ?, 'aktiv')
            ");
            $stmt->execute([$mitarbeiterId, $von, $bis]);

            $betroffeneKurse = [];

            if ($rolle === "Trainer") {
                $stmt = $pdo->prepare("
                    SELECT kurs_id, datum, uhrzeit
                    FROM kurs
                    WHERE trainer_id = ?
                    AND datum BETWEEN ? AND ?
                    AND status = 'normal'
                    ORDER BY datum, uhrzeit
                ");
                $stmt->execute([$mitarbeiterId, $von, $bis]);
                $betroffeneKurse = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            $anzahlVertretung = 0;
            $anzahlOhneVertretung = 0;

            foreach ($betroffeneKurse as $kurs) {
                $kursId = $kurs["kurs_id"];

                $stmtUpdate = $pdo->prepare("
                    UPDATE kurs
                    SET status = 'vertretung_noetig',
                        ersatztrainer_id = NULL
                    WHERE kurs_id = ?
                ");
                $stmtUpdate->execute([$kursId]);

                $ersatz = $service->sucheVertretung($kursId);

                if ($ersatz) {
                    $service->trainerZuweisen($kursId, $ersatz["mitarbeiter_id"]);
                    $anzahlVertretung++;

                    $details[] = "Kurs am " . date("d.m.Y", strtotime($kurs["datum"])) .
                        " um " . substr($kurs["uhrzeit"], 0, 5) .
                        ": Vertretung gefunden: " . $ersatz["name"];
                } else {
                    $anzahlOhneVertretung++;

                    $details[] = "Kurs am " . date("d.m.Y", strtotime($kurs["datum"])) .
                        " um " . substr($kurs["uhrzeit"], 0, 5) .
                        ": Keine passende Vertretung gefunden.";
                }
            }

            $pdo->commit();

            if ($rolle === "Trainer") {
                $success = "Krankmeldung wurde gespeichert. Betroffene Kurse: " .
                    count($betroffeneKurse) .
                    ". Vertretung gefunden: " .
                    $anzahlVertretung .
                    ". Ohne Vertretung: " .
                    $anzahlOhneVertretung .
                    ".";
            } else {
                $success = "Krankmeldung wurde gespeichert. Für Sekretariat werden keine Kurse ersetzt.";
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Fehler beim Speichern der Krankmeldung: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Krankmeldung eintragen</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<div class="container">
    <h1>Krankmeldung eintragen</h1>

    <p><a href="dashboard.php">Zurück zum Dashboard</a></p>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <?php if (!empty($details)): ?>
        <h2>Ergebnis der automatischen Vertretungssuche</h2>
        <ul>
            <?php foreach ($details as $detail): ?>
                <li><?= htmlspecialchars($detail) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post">
        <p><strong>Mitarbeiter:</strong> <?= htmlspecialchars($mitarbeiterName) ?></p>
        <p><strong>Rolle:</strong> <?= htmlspecialchars($rolle) ?></p>

        <label>Datum von</label>
        <input type="date" name="datum_von" required>

        <label>Datum bis</label>
        <input type="date" name="datum_bis" required>

        <button type="submit">Krankmeldung speichern</button>
    </form>
</div>

</body>
</html>
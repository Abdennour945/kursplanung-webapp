<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../includes/auth.php";
require_once "../config/db.php";
require_once "../src/Planungsservice.php";

requireLogin();
requireRole(["Sekretariat"]);

$service = new Planungsservice($pdo);

$error = "";
$success = "";

$auslastungsDatum = $_POST["datum"] ?? date("Y-m-d");

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

$stmtTrainer = $pdo->prepare("
    SELECT 
        m.mitarbeiter_id,
        m.name,
        m.rolle,
        m.standort_id,
        st.name AS trainer_standort,
        a.bezeichnung AS arbeitszeitmodell,
        a.max_kursstunden,
        COUNT(k.kurs_id) AS kursstunden
    FROM mitarbeiter m
    JOIN arbeitszeitmodell a
        ON m.arbeitszeitmodell_id = a.arbeitszeitmodell_id
    LEFT JOIN standort st
        ON m.standort_id = st.standort_id
    LEFT JOIN kurs k
        ON (k.trainer_id = m.mitarbeiter_id OR k.ersatztrainer_id = m.mitarbeiter_id)
        AND k.datum BETWEEN 
            date_trunc('week', ?::date)::date
            AND (date_trunc('week', ?::date) + interval '6 days')::date
    WHERE m.rolle = 'Trainer'
    AND m.status = 'aktiv'
    GROUP BY 
        m.mitarbeiter_id,
        m.name,
        m.rolle,
        m.standort_id,
        st.name,
        a.bezeichnung,
        a.max_kursstunden
    ORDER BY st.name, m.mitarbeiter_id
");

$stmtTrainer->execute([$auslastungsDatum, $auslastungsDatum]);
$trainerListe = $stmtTrainer->fetchAll(PDO::FETCH_ASSOC);

$raeume = $pdo->query("
    SELECT 
        r.raum_id,
        r.name AS raum_name,
        r.nummer,
        r.standort_id,
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

    if (empty($datum) || empty($uhrzeit) || empty($raumId) || empty($trainerId)) {
        $error = "Bitte alle Felder ausfüllen.";
    } else {
        $stmt = $pdo->prepare("
            SELECT 
                m.standort_id,
                s.name AS standort_name
            FROM mitarbeiter m
            LEFT JOIN standort s ON m.standort_id = s.standort_id
            WHERE m.mitarbeiter_id = ?
            AND m.rolle = 'Trainer'
            AND m.status = 'aktiv'
        ");
        $stmt->execute([$trainerId]);
        $trainerStandort = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("
            SELECT 
                r.standort_id,
                s.name AS standort_name
            FROM raum r
            JOIN standort s ON r.standort_id = s.standort_id
            WHERE r.raum_id = ?
        ");
        $stmt->execute([$raumId]);
        $raumStandort = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$trainerStandort || empty($trainerStandort["standort_id"])) {
            $error = "Dieser Trainer hat noch keinen Standort.";
        } elseif (!$raumStandort) {
            $error = "Der Raum wurde nicht gefunden.";
        } elseif ($trainerStandort["standort_id"] != $raumStandort["standort_id"]) {
            $error = "Der Trainer und der Raum müssen im gleichen Standort sein.";
        } elseif (!$service->istRaumVerfuegbar($raumId, $datum, $uhrzeit)) {
            $error = "Der Raum ist zu dieser Zeit bereits belegt.";
        } else {
            $trainerFehler = $service->pruefeTrainer($trainerId, $datum, $uhrzeit, $raumId);

            if ($trainerFehler !== null) {
                $error = $trainerFehler;
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO kurs (datum, uhrzeit, dauer_minuten, raum_id, trainer_id, status)
                    VALUES (?, ?, 60, ?, ?, 'normal')
                ");
                $stmt->execute([$datum, $uhrzeit, $raumId, $trainerId]);

                $success = "Kurs wurde erfolgreich angelegt.";

                $stmtTrainer->execute([$datum, $datum]);
                $trainerListe = $stmtTrainer->fetchAll(PDO::FETCH_ASSOC);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Kurs anlegen</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<div class="container">
    <h1>Kurs anlegen</h1>

    <p><a href="dashboard.php">Zurück zum Dashboard</a></p>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Datum</label>
        <input 
            type="date" 
            name="datum" 
            value="<?= htmlspecialchars($_POST["datum"] ?? date("Y-m-d")) ?>" 
            required
        >

        <label>Uhrzeit</label>
        <select name="uhrzeit" required>
            <option value="">Bitte auswählen</option>

            <?php foreach ($zeiten as $zeit): ?>
                <?php
                    $ende = date("H:i", strtotime("2000-01-01 " . $zeit . " +1 hour"));
                    $selected = (($_POST["uhrzeit"] ?? "") === $zeit) ? "selected" : "";
                ?>
                <option value="<?= $zeit ?>" <?= $selected ?>>
                    <?= $zeit ?> - <?= $ende ?>
                </option>
            <?php endforeach; ?>
        </select>

        <p><strong>Dauer:</strong> Jeder Kurs dauert 60 Minuten.</p>

        <label>Trainer</label>
        <select name="trainer_id" id="trainer_id" required onchange="filterRaeumeNachTrainer()">
            <option value="">Bitte auswählen</option>

            <?php foreach ($trainerListe as $trainer): ?>
                <?php
                    $voll = ((int)$trainer["kursstunden"] >= (int)$trainer["max_kursstunden"]);
                    $selected = (($_POST["trainer_id"] ?? "") == $trainer["mitarbeiter_id"]) ? "selected" : "";
                    $standortName = $trainer["trainer_standort"] ?? "Kein Standort";
                ?>

                <option 
                    value="<?= $trainer["mitarbeiter_id"] ?>" 
                    data-standort-id="<?= htmlspecialchars($trainer["standort_id"] ?? "") ?>"
                    <?= $selected ?>
                    <?= $voll ? "disabled" : "" ?>
                >
                    <?= htmlspecialchars(
                        $trainer["name"] .
                        " - " .
                        $standortName .
                        " (" .
                        $trainer["arbeitszeitmodell"] .
                        ": " .
                        $trainer["kursstunden"] .
                        "/" .
                        $trainer["max_kursstunden"] .
                        " Std)"
                    ) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Raum / Standort</label>
        <select name="raum_id" id="raum_id" required>
            <option value="">Bitte zuerst Trainer auswählen</option>

            <?php foreach ($raeume as $raum): ?>
                <?php
                    $selected = (($_POST["raum_id"] ?? "") == $raum["raum_id"]) ? "selected" : "";
                ?>
                <option 
                    value="<?= $raum["raum_id"] ?>" 
                    data-standort-id="<?= htmlspecialchars($raum["standort_id"]) ?>"
                    <?= $selected ?>
                >
                    <?= htmlspecialchars($raum["standort_name"] . " - " . $raum["raum_name"] . " (" . $raum["nummer"] . ")") ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Kurs speichern</button>
    </form>

    <h2>Trainer-Auslastung</h2>

    <table>
        <tr>
            <th>Trainer</th>
            <th>Standort</th>
            <th>Arbeitszeitmodell</th>
            <th>Aktuelle Kursstunden</th>
            <th>Max. Kursstunden</th>
            <th>Frei</th>
        </tr>

        <?php foreach ($trainerListe as $trainer): ?>
            <?php
                $frei = (int)$trainer["max_kursstunden"] - (int)$trainer["kursstunden"];
            ?>
            <tr>
                <td><?= htmlspecialchars($trainer["name"]) ?></td>
                <td><?= htmlspecialchars($trainer["trainer_standort"] ?? "-") ?></td>
                <td><?= htmlspecialchars($trainer["arbeitszeitmodell"]) ?></td>
                <td><?= htmlspecialchars($trainer["kursstunden"]) ?></td>
                <td><?= htmlspecialchars($trainer["max_kursstunden"]) ?></td>
                <td><?= htmlspecialchars(max(0, $frei)) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<script>
function filterRaeumeNachTrainer() {
    const trainerSelect = document.getElementById("trainer_id");
    const raumSelect = document.getElementById("raum_id");

    const selectedTrainer = trainerSelect.options[trainerSelect.selectedIndex];
    const trainerStandortId = selectedTrainer ? selectedTrainer.dataset.standortId : "";

    let ersterPassenderRaum = "";
    let aktuellerRaumPasst = false;

    for (const option of raumSelect.options) {
        if (option.value === "") {
            option.hidden = false;
            option.disabled = false;
            continue;
        }

        const raumStandortId = option.dataset.standortId;
        const passt = trainerStandortId !== "" && raumStandortId === trainerStandortId;

        option.hidden = !passt;
        option.disabled = !passt;

        if (passt && ersterPassenderRaum === "") {
            ersterPassenderRaum = option.value;
        }

        if (option.selected && passt) {
            aktuellerRaumPasst = true;
        }
    }

    if (trainerStandortId === "") {
        raumSelect.value = "";
        return;
    }

    if (!aktuellerRaumPasst && ersterPassenderRaum !== "") {
        raumSelect.value = ersterPassenderRaum;
    }
}

filterRaeumeNachTrainer();
</script>

</body>
</html>
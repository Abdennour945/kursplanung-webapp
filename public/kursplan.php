<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../includes/auth.php";
require_once "../config/db.php";

requireLogin();

$params = [];

$sql = "
    SELECT 
        k.kurs_id,
        k.datum,
        k.uhrzeit,
        k.dauer_minuten,
        k.status,
        r.name AS raum_name,
        r.nummer AS raum_nummer,
        s.name AS standort_name,
        t.name AS trainer_name,
        e.name AS ersatztrainer_name,
        a.bezeichnung AS arbeitszeitmodell
    FROM kurs k
    JOIN raum r ON k.raum_id = r.raum_id
    JOIN standort s ON r.standort_id = s.standort_id
    JOIN mitarbeiter t ON k.trainer_id = t.mitarbeiter_id
    JOIN arbeitszeitmodell a ON t.arbeitszeitmodell_id = a.arbeitszeitmodell_id
    LEFT JOIN mitarbeiter e ON k.ersatztrainer_id = e.mitarbeiter_id
";

if ($_SESSION["rolle"] === "Trainer") {
    $sql .= " WHERE k.trainer_id = ? OR k.ersatztrainer_id = ?";
    $params[] = $_SESSION["user_id"];
    $params[] = $_SESSION["user_id"];
}

$sql .= " ORDER BY k.datum, k.uhrzeit, s.name";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$kurse = $stmt->fetchAll(PDO::FETCH_ASSOC);

$darfBearbeiten = ($_SESSION["rolle"] === "Sekretariat" || $_SESSION["rolle"] === "Administrator");
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Kursplan</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<div class="container">
    <h1>
        <?php if ($_SESSION["rolle"] === "Trainer"): ?>
            Meine Kurse
        <?php else: ?>
            Kursplan
        <?php endif; ?>
    </h1>

    <p><a href="dashboard.php">Zurück zum Dashboard</a></p>

    <?php if (count($kurse) === 0): ?>
        <p>Keine Kurse vorhanden.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Datum</th>
                <th>Uhrzeit</th>
                <th>Dauer</th>
                <th>Standort</th>
                <th>Raum</th>
                <th>Mitarbeiter</th>
                <th>Arbeitszeitmodell</th>
                <th>vertretung</th>
                <th>Status</th>

                <?php if ($darfBearbeiten): ?>
                    <th>Aktion</th>
                <?php endif; ?>
            </tr>

            <?php foreach ($kurse as $kurs): ?>
                <?php
                    $klasse = "kurs-normal";

                    if ($kurs["status"] === "vertretung_noetig" || $kurs["status"] === "vertretung") {
                        $klasse = "kurs-vertretung";
                    }

                    $start = substr($kurs["uhrzeit"], 0, 5);
                    $ende = date("H:i", strtotime("2000-01-01 " . $start . " +1 hour"));
                ?>

                <tr class="<?= $klasse ?>">
                    <td><?= htmlspecialchars(date("d.m.Y", strtotime($kurs["datum"]))) ?></td>
                    <td><?= htmlspecialchars($start . " - " . $ende) ?></td>
                    <td><?= htmlspecialchars($kurs["dauer_minuten"]) ?> Minuten</td>
                    <td><?= htmlspecialchars($kurs["standort_name"]) ?></td>
                    <td><?= htmlspecialchars($kurs["raum_name"] . " (" . $kurs["raum_nummer"] . ")") ?></td>
                    <td><?= htmlspecialchars($kurs["trainer_name"]) ?></td>
                    <td><?= htmlspecialchars($kurs["arbeitszeitmodell"]) ?></td>
                    <td><?= htmlspecialchars($kurs["ersatztrainer_name"] ?? "-") ?></td>
                    <td><?= htmlspecialchars($kurs["status"]) ?></td>

                    <?php if ($darfBearbeiten): ?>
                        <td>
                            <a href="kurs_edit.php?id=<?= $kurs["kurs_id"] ?>">Bearbeiten</a>
                            |
                            <a 
                                href="kurs_delete.php?id=<?= $kurs["kurs_id"] ?>" 
                                onclick="return confirm('Diesen Kurs wirklich löschen?');"
                            >
                                Löschen
                            </a>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
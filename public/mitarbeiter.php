<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

requireLogin();
requireRole(["Administrator"]);

$stmt = $pdo->query("
    SELECT 
        m.mitarbeiter_id,
        m.name,
        m.email,
        m.rolle,
        m.status,
        a.bezeichnung AS arbeitszeitmodell,
        b.name AS buero_name,
        b.nummer AS buero_nummer,
        s.name AS buero_standort
    FROM mitarbeiter m
    LEFT JOIN arbeitszeitmodell a
    ON m.arbeitszeitmodell_id = a.arbeitszeitmodell_id
    LEFT JOIN buero b
    ON m.buero_id = b.buero_id
    LEFT JOIN standort s
    ON b.standort_id = s.standort_id
    ORDER BY m.rolle, m.mitarbeiter_id
");

$mitarbeiterListe = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Mitarbeiter verwalten</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<div class="container">
    <h1>Mitarbeiter verwalten</h1>

    <p><a href="dashboard.php">Zurück zum Dashboard</a></p>

    <p>
        <a href="mitarbeiter_create.php">Neuen Mitarbeiter anlegen</a>
    </p>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Rolle</th>
            <th>Arbeitszeitmodell</th>
            <th>Büro</th>
            <th>Status</th>
            <th>Aktion</th>
        </tr>

        <?php foreach ($mitarbeiterListe as $m): ?>
            <tr>
                <td><?= htmlspecialchars($m["mitarbeiter_id"]) ?></td>
                <td><?= htmlspecialchars($m["name"]) ?></td>
                <td><?= htmlspecialchars($m["email"]) ?></td>
                <td><?= htmlspecialchars($m["rolle"]) ?></td>
                <td><?= htmlspecialchars($m["arbeitszeitmodell"] ?? "-") ?></td>

                <td>
                    <?php if ($m["rolle"] === "Sekretariat" && !empty($m["buero_name"])): ?>
                        <?= htmlspecialchars($m["buero_standort"] . " - " . $m["buero_name"] . " (" . $m["buero_nummer"] . ")") ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>

                <td><?= htmlspecialchars($m["status"]) ?></td>
                <td>
                    <a href="mitarbeiter_edit.php?id=<?= $m["mitarbeiter_id"] ?>">Bearbeiten</a>
                    |

                    <?php if ($m["status"] === "aktiv"): ?>
                        <a 
                            href="mitarbeiter_status.php?id=<?= $m["mitarbeiter_id"] ?>&status=inaktiv"
                            onclick="return confirm('Mitarbeiter wirklich deaktivieren?');"
                        >
                            Deaktivieren
                        </a>
                    <?php else: ?>
                        <a 
                            href="mitarbeiter_status.php?id=<?= $m["mitarbeiter_id"] ?>&status=aktiv"
                            onclick="return confirm('Mitarbeiter wirklich aktivieren?');"
                        >
                            Aktivieren
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>
<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

requireLogin();
requireRole(["Administrator"]);

$error = "";

$arbeitszeitmodelle = $pdo->query("
    SELECT arbeitszeitmodell_id, bezeichnung
    FROM arbeitszeitmodell
    ORDER BY arbeitszeitmodell_id
")->fetchAll(PDO::FETCH_ASSOC);

$standorte = $pdo->query("
    SELECT standort_id, name
    FROM standort
    ORDER BY name
")->fetchAll(PDO::FETCH_ASSOC);

$bueros = $pdo->query("
    SELECT 
        b.buero_id,
        b.name AS buero_name,
        b.nummer,
        s.name AS standort_name
    FROM buero b
    JOIN standort s ON b.standort_id = s.standort_id
    ORDER BY s.name, b.name
")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $passwort = $_POST["passwort"];
    $rolle = $_POST["rolle"];
    $arbeitszeitmodellId = $_POST["arbeitszeitmodell_id"];
    $status = $_POST["status"];

    $standortId = $_POST["standort_id"] ?? null;
    $bueroId = $_POST["buero_id"] ?? null;

    if ($standortId === "") {
        $standortId = null;
    }

    if ($bueroId === "") {
        $bueroId = null;
    }

    if (empty($name) || empty($email) || empty($passwort) || empty($rolle) || empty($arbeitszeitmodellId) || empty($status)) {
        $error = "Bitte alle Pflichtfelder ausfüllen.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Bitte eine gültige Email eingeben.";
    } elseif ($rolle === "Trainer" && empty($standortId)) {
        $error = "Bitte für Trainer einen Standort auswählen.";
    } elseif ($rolle === "Sekretariat" && empty($bueroId)) {
        $error = "Bitte für Sekretariat ein Büro auswählen.";
    } else {
        try {
            if ($rolle === "Trainer") {
                $bueroId = null;
            } elseif ($rolle === "Sekretariat") {
                $standortId = null;
            } else {
                $standortId = null;
                $bueroId = null;
            }

            $passwortHash = password_hash($passwort, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO mitarbeiter 
                (name, email, passwort, rolle, arbeitszeitmodell_id, status, standort_id, buero_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $name,
                $email,
                $passwortHash,
                $rolle,
                $arbeitszeitmodellId,
                $status,
                $standortId,
                $bueroId
            ]);

            header("Location: mitarbeiter.php");
            exit;
        } catch (PDOException $e) {
            $error = "Fehler beim Speichern. Email existiert vielleicht schon.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Neuen Mitarbeiter anlegen</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<div class="container">
    <h1>Neuen Mitarbeiter anlegen</h1>

    <p><a href="mitarbeiter.php">Zurück zur Mitarbeiterliste</a></p>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Name</label>
        <input type="text" name="name" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Passwort</label>
        <input type="password" name="passwort" required>

        <label>Rolle</label>
        <select name="rolle" id="rolle" required onchange="zeigeFelder()">
            <option value="">Bitte auswählen</option>
            <option value="Trainer">Trainer</option>
            <option value="Sekretariat">Sekretariat</option>
            <option value="Administrator">Administrator</option>
        </select>

        <label>Arbeitszeitmodell</label>
        <select name="arbeitszeitmodell_id" required>
            <option value="">Bitte auswählen</option>
            <?php foreach ($arbeitszeitmodelle as $modell): ?>
                <option value="<?= $modell["arbeitszeitmodell_id"] ?>">
                    <?= htmlspecialchars($modell["bezeichnung"]) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div id="standortFeld">
            <label>Standort für Trainer</label>
            <select name="standort_id">
                <option value="">Bitte auswählen</option>
                <?php foreach ($standorte as $standort): ?>
                    <option value="<?= $standort["standort_id"] ?>">
                        <?= htmlspecialchars($standort["name"]) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="bueroFeld">
            <label>Büro für Sekretariat</label>
            <select name="buero_id">
                <option value="">Bitte auswählen</option>
                <?php foreach ($bueros as $buero): ?>
                    <option value="<?= $buero["buero_id"] ?>">
                        <?= htmlspecialchars($buero["standort_name"] . " - " . $buero["buero_name"] . " (" . $buero["nummer"] . ")") ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <label>Status</label>
        <select name="status" required>
            <option value="aktiv">aktiv</option>
            <option value="inaktiv">inaktiv</option>
        </select>

        <button type="submit">Mitarbeiter speichern</button>
    </form>
</div>

<script>
function zeigeFelder() {
    const rolle = document.getElementById("rolle").value;
    const standortFeld = document.getElementById("standortFeld");
    const bueroFeld = document.getElementById("bueroFeld");

    standortFeld.style.display = rolle === "Trainer" ? "block" : "none";
    bueroFeld.style.display = rolle === "Sekretariat" ? "block" : "none";
}

zeigeFelder();
</script>

</body>
</html>
<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

requireLogin();
requireRole(["Administrator"]);

$error = "";

if (!isset($_GET["id"])) {
    die("Keine Mitarbeiter-ID angegeben.");
}

$id = $_GET["id"];

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

$stmt = $pdo->prepare("
    SELECT *
    FROM mitarbeiter
    WHERE mitarbeiter_id = ?
");
$stmt->execute([$id]);
$mitarbeiter = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mitarbeiter) {
    die("Mitarbeiter nicht gefunden.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $rolle = $_POST["rolle"];
    $arbeitszeitmodellId = $_POST["arbeitszeitmodell_id"];
    $status = $_POST["status"];
    $neuesPasswort = $_POST["passwort"];

    $standortId = $_POST["standort_id"] ?? null;
    $bueroId = $_POST["buero_id"] ?? null;

    if ($standortId === "") {
        $standortId = null;
    }

    if ($bueroId === "") {
        $bueroId = null;
    }

    if (empty($name) || empty($email) || empty($rolle) || empty($arbeitszeitmodellId) || empty($status)) {
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

            if (!empty($neuesPasswort)) {
                $passwortHash = password_hash($neuesPasswort, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("
                    UPDATE mitarbeiter
                    SET name = ?,
                        email = ?,
                        passwort = ?,
                        rolle = ?,
                        arbeitszeitmodell_id = ?,
                        status = ?,
                        standort_id = ?,
                        buero_id = ?
                    WHERE mitarbeiter_id = ?
                ");

                $stmt->execute([
                    $name,
                    $email,
                    $passwortHash,
                    $rolle,
                    $arbeitszeitmodellId,
                    $status,
                    $standortId,
                    $bueroId,
                    $id
                ]);
            } else {
                $stmt = $pdo->prepare("
                    UPDATE mitarbeiter
                    SET name = ?,
                        email = ?,
                        rolle = ?,
                        arbeitszeitmodell_id = ?,
                        status = ?,
                        standort_id = ?,
                        buero_id = ?
                    WHERE mitarbeiter_id = ?
                ");

                $stmt->execute([
                    $name,
                    $email,
                    $rolle,
                    $arbeitszeitmodellId,
                    $status,
                    $standortId,
                    $bueroId,
                    $id
                ]);
            }

            header("Location: mitarbeiter.php");
            exit;
        } catch (PDOException $e) {
            $error = "Fehler beim Aktualisieren. Email existiert vielleicht schon.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Mitarbeiter bearbeiten</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<div class="container">
    <h1>Mitarbeiter bearbeiten</h1>

    <p><a href="mitarbeiter.php">Zurück zur Mitarbeiterliste</a></p>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Name</label>
        <input 
            type="text" 
            name="name" 
            value="<?= htmlspecialchars($mitarbeiter["name"]) ?>" 
            required
        >

        <label>Email</label>
        <input 
            type="email" 
            name="email" 
            value="<?= htmlspecialchars($mitarbeiter["email"]) ?>" 
            required
        >

        <label>Neues Passwort</label>
        <input type="password" name="passwort">
        <p>Leer lassen, wenn das Passwort nicht geändert werden soll.</p>

        <label>Rolle</label>
        <select name="rolle" id="rolle" required onchange="zeigeFelder()">
            <option value="Trainer" <?= $mitarbeiter["rolle"] === "Trainer" ? "selected" : "" ?>>Trainer</option>
            <option value="Sekretariat" <?= $mitarbeiter["rolle"] === "Sekretariat" ? "selected" : "" ?>>Sekretariat</option>
            <option value="Administrator" <?= $mitarbeiter["rolle"] === "Administrator" ? "selected" : "" ?>>Administrator</option>
        </select>

        <label>Arbeitszeitmodell</label>
        <select name="arbeitszeitmodell_id" required>
            <?php foreach ($arbeitszeitmodelle as $modell): ?>
                <option 
                    value="<?= $modell["arbeitszeitmodell_id"] ?>"
                    <?= $mitarbeiter["arbeitszeitmodell_id"] == $modell["arbeitszeitmodell_id"] ? "selected" : "" ?>
                >
                    <?= htmlspecialchars($modell["bezeichnung"]) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div id="standortFeld">
            <label>Standort für Trainer</label>
            <select name="standort_id">
                <option value="">Bitte auswählen</option>
                <?php foreach ($standorte as $standort): ?>
                    <option 
                        value="<?= $standort["standort_id"] ?>"
                        <?= ($mitarbeiter["standort_id"] ?? null) == $standort["standort_id"] ? "selected" : "" ?>
                    >
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
                    <option 
                        value="<?= $buero["buero_id"] ?>"
                        <?= ($mitarbeiter["buero_id"] ?? null) == $buero["buero_id"] ? "selected" : "" ?>
                    >
                        <?= htmlspecialchars($buero["standort_name"] . " - " . $buero["buero_name"] . " (" . $buero["nummer"] . ")") ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <label>Status</label>
        <select name="status" required>
            <option value="aktiv" <?= $mitarbeiter["status"] === "aktiv" ? "selected" : "" ?>>aktiv</option>
            <option value="inaktiv" <?= $mitarbeiter["status"] === "inaktiv" ? "selected" : "" ?>>inaktiv</option>
        </select>

        <button type="submit">Änderungen speichern</button>
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
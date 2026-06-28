<?php
require_once "../includes/auth.php";
requireLogin();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Kursplanung</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<div class="container">
    <h1>Dashboard</h1>

    <p>Willkommen, <?= htmlspecialchars($_SESSION["name"]) ?></p>
    <p>Rolle: <?= htmlspecialchars($_SESSION["rolle"]) ?></p>

    <div class="menu">

        <?php if ($_SESSION["rolle"] === "Sekretariat"): ?>
            <a href="kursplan.php">Kursplan anzeigen</a>
            <a href="kurs_anlegen.php">Kurs anlegen</a>
            <a href="krankmeldung.php">Meine Krankmeldung eintragen</a>
            
        <?php endif; ?>

        <?php if ($_SESSION["rolle"] === "Administrator"): ?>
            <a href="mitarbeiter.php">Mitarbeiter verwalten</a>
        
            <a href="kursplan.php">Kursplan anzeigen</a>
        <?php endif; ?>

        <?php if ($_SESSION["rolle"] === "Trainer"): ?>
            <a href="kursplan.php">Meine Kurse anzeigen</a>
            <a href="krankmeldung.php">Meine Krankmeldung eintragen</a>
        <?php endif; ?>

        <a href="logout.php">Logout</a>
    </div>
</div>

</body>
</html>
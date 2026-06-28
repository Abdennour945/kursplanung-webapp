<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

requireLogin();
requireRole(["Sekretariat", "Administrator"]);

if (!isset($_GET["id"])) {
    die("Keine Kurs-ID angegeben.");
}

$kursId = $_GET["id"];

$stmt = $pdo->prepare("DELETE FROM kurs WHERE kurs_id = ?");
$stmt->execute([$kursId]);

header("Location: kursplan.php");
exit;
<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

requireLogin();
requireRole(["Administrator"]);

if (!isset($_GET["id"]) || !isset($_GET["status"])) {
    die("Fehlende Daten.");
}

$id = $_GET["id"];
$status = $_GET["status"];

if (!in_array($status, ["aktiv", "inaktiv"])) {
    die("Ungültiger Status.");
}

$stmt = $pdo->prepare("
    UPDATE mitarbeiter
    SET status = ?
    WHERE mitarbeiter_id = ?
");

$stmt->execute([$status, $id]);

header("Location: mitarbeiter.php");
exit;
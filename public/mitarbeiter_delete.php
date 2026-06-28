<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

requireLogin();
requireRole(["Administrator"]);

if (!isset($_GET["id"])) {
    die("Keine Mitarbeiter-ID angegeben.");
}

$id = $_GET["id"];

$stmt = $pdo->prepare("
    UPDATE mitarbeiter
    SET status = 'inaktiv'
    WHERE mitarbeiter_id = ?
");

$stmt->execute([$id]);

header("Location: mitarbeiter.php");
exit;
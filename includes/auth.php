<?php
session_start();

function requireLogin() {
    if (!isset($_SESSION["user_id"])) {
        header("Location: login.php");
        exit;
    }
}

function requireRole(array $roles) {
    if (!isset($_SESSION["rolle"]) || !in_array($_SESSION["rolle"], $roles)) {
        die("Kein Zugriff");
    }
}
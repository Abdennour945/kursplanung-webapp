<?php
require_once "../config/db.php";
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $passwort = $_POST["passwort"];

    $stmt = $pdo->prepare("
        SELECT *
        FROM mitarbeiter
        WHERE email = ?
        AND status = 'aktiv'
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($passwort, $user["passwort"])) {
        $_SESSION["user_id"] = $user["mitarbeiter_id"];
        $_SESSION["name"] = $user["name"];
        $_SESSION["rolle"] = $user["rolle"];

        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Login fehlgeschlagen";
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Login - Kursplanung</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="login-page">

<div class="login-card">
    <h1>Kursplanung</h1>
    <p class="login-subtitle">Bitte melden Sie sich an</p>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Email</label>
        <input type="email" name="email" placeholder="email@example.de" required>

        <label>Passwort</label>
        <input type="password" name="passwort" placeholder="Passwort eingeben" required>

        <button type="submit">Einloggen</button>
    </form>
</div>

</body>
</html>
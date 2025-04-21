<link rel="stylesheet" href="../css/style.css">



<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Freelance Platform</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header>
    <h1>Freelance Platform</h1>
    <nav>
        <a href="index.php">Início</a> |
        <a href="services.php">Serviços</a> |
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="dashboard.php">Painel</a> |
            <a href="logout.php">Logout (<?= htmlspecialchars($_SESSION['username']) ?>)</a>
        <?php else: ?>
            <a href="login.php">Login</a> |
            <a href="register.php">Registar</a>
        <?php endif; ?>
    </nav>
    <hr>
</header>

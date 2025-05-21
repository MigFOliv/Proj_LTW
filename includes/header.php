<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// caminho para os ficheiros CSS e JS
$isInPagesFolder = strpos($_SERVER['PHP_SELF'], '/pages/') !== false;
$cssPath = $isInPagesFolder ? '../css/style.css' : 'css/style.css';
$jsPath = $isInPagesFolder ? '../js/main.js' : 'js/main.js';
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Freelancer Platform</title>
    <link rel="stylesheet" href="<?= $cssPath ?>">
</head>
<body>
<header>
    <h1>Freelancer Platform</h1>
    <nav>
        <a href="/index.php">🏠 Início</a>
        <a href="/pages/services.php">📋 Serviços</a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/pages/dashboard.php">🎯 Painel</a>
            <a href="/pages/my_orders.php">🧾 Minhas Compras</a>
            <a href="/pages/my_requests.php">📨 Pedidos Recebidos</a>
            <a href="/pages/favorites.php">⭐ Favoritos</a>
            <a href="/pages/messages.php">💬 Mensagens</a>
            <a href="/pages/profile.php">👤 Perfil</a>
            <button class="toggle-dark" onclick="toggleDarkMode()">🌙 Modo Escuro</button>

            <?php if (!empty($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                <a href="/pages/admin.php">⚙️ Admin</a>
            <?php endif; ?>

            <a href="/logout.php">🚪 Logout (<?= htmlspecialchars($_SESSION['username']) ?>)</a>
        <?php else: ?>
            <a href="/login.php">🔐 Login</a>
            <a href="/register.php">📝 Registar</a>
        <?php endif; ?>
    </nav>
</header>
<script src="<?= $jsPath ?>"></script>

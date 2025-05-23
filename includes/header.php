<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<header class="site-header">
  <div class="header-container">
    <div class="site-title">
      <a href="/index.php">FreeLanceX</a>
    </div>

    <nav class="nav-links" role="navigation">
      <a href="/index.php">🏠 Início</a>
      <a href="/pages/services.php">📋 Serviços</a>

      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="/pages/dashboard.php">🎯 Painel</a>
        <a href="/pages/my_orders.php">🧾 Minhas Compras</a>
        <a href="/pages/my_requests.php">📨 Pedidos Recebidos</a>
        <a href="/pages/favorites.php">⭐ Favoritos</a>
        <a href="/pages/messages.php">💬 Mensagens</a>
        <a href="/pages/profile.php">👤 Perfil</a>

        <?php if (!empty($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
          <a href="/pages/admin.php">⚙️ Admin</a>
        <?php endif; ?>

        <a href="/logout.php">🚪 Logout (<?= htmlspecialchars($_SESSION['username']) ?>)</a>
      <?php else: ?>
        <a href="/login.php">🔐 Login</a>
        <a href="/register.php">📝 Registar</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

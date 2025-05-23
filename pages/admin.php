<?php
require_once '../includes/auth.php';
require_once '../includes/csrf.php';
require_login();
require_once '../includes/db.php';
require_once '../includes/head.php';
require_once '../includes/header.php';

// Verifica se é admin
if ($_SESSION['is_admin'] != 1) {
    echo "<main class='dashboard-container'><p class='error'>❌ Acesso restrito. Apenas administradores podem aceder.</p></main>";
    include '../includes/footer.php';
    exit();
}

// Promoção de utilizador a admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['promote_id'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die("Token CSRF inválido.");
    }

    $promote_id = (int) $_POST['promote_id'];
    if ($promote_id > 0) {
        $stmt = $db->prepare("UPDATE users SET is_admin = 1 WHERE id = :id");
        $stmt->execute([':id' => $promote_id]);
        $message = "✅ Utilizador promovido a admin.";
    }
}

// Nova categoria
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_category'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die("Token CSRF inválido.");
    }

    $name = trim($_POST['new_category']);
    if (!empty($name) && strlen($name) <= 50) {
        $stmt = $db->prepare("INSERT OR IGNORE INTO categories (name) VALUES (:name)");
        $stmt->execute([':name' => $name]);
        $message = "✅ Categoria adicionada com sucesso.";
    } else {
        $error = "❌ Nome de categoria inválido.";
    }
}

// Obter dados
$users = $db->query("SELECT id, username, email, is_admin FROM users ORDER BY username ASC")->fetchAll(PDO::FETCH_ASSOC);
$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="dashboard-container">
    <h2>⚙️ Painel de Administração</h2>

    <?php if (isset($message)): ?>
        <p class="success"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <section style="margin-top: 2rem;">
        <h3>👥 Utilizadores</h3>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr><th>Username</th><th>Email</th><th>Tipo</th><th>Ação</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['username']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= $u['is_admin'] ? 'Admin' : 'Utilizador' ?></td>
                            <td>
                                <?php if (!$u['is_admin']): ?>
                                    <form method="post" class="inline-form">
                                        <input type="hidden" name="promote_id" value="<?= $u['id'] ?>">
                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                        <button class="primary-btn" type="submit">👑 Promover</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <hr>

    <section style="margin-top: 2rem;">
        <h3>📁 Categorias</h3>
        <ul class="stats-list">
            <?php foreach ($categories as $c): ?>
                <li><?= htmlspecialchars($c['name']) ?></li>
            <?php endforeach; ?>
        </ul>

        <form method="post" class="auth-form" style="max-width: 400px; margin-top: 1.5rem;">
            <label for="new_category">Nova categoria:</label>
            <input type="text" name="new_category" required maxlength="50">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <button type="submit" class="primary-btn">➕ Adicionar</button>
        </form>
    </section>
</main>

<?php include '../includes/footer.php'; ?>

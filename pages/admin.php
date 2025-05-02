<?php
require_once '../includes/auth.php';
require_login();
require_once '../includes/db.php';
include '../includes/header.php';

// Verifica se é admin
if ($_SESSION['is_admin'] != 1) {
    echo "<p>Acesso restrito. Apenas administradores podem aceder.</p>";
    include '../includes/footer.php';
    exit();
}

// Lidar com promoção
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['promote_id'])) {
    $stmt = $db->prepare("UPDATE users SET is_admin = 1 WHERE id = :id");
    $stmt->execute([':id' => $_POST['promote_id']]);
    echo "<p style='color:green;'>Utilizador promovido a admin.</p>";
}

// Lidar com nova categoria
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_category'])) {
    $name = trim($_POST['new_category']);
    if (!empty($name)) {
        $stmt = $db->prepare("INSERT OR IGNORE INTO categories (name) VALUES (:name)");
        $stmt->execute([':name' => $name]);
        echo "<p style='color:green;'>Categoria adicionada.</p>";
    }
}

// Obter utilizadores
$users = $db->query("SELECT id, username, email, is_admin FROM users ORDER BY username ASC")->fetchAll(PDO::FETCH_ASSOC);

// Obter categorias
$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<main>
<h2>⚙️ Painel de Administração</h2>

<h3>👥 Utilizadores</h3>
<table border="1" cellpadding="6">
    <tr><th>Username</th><th>Email</th><th>Tipo</th><th>Ação</th></tr>
    <?php foreach ($users as $u): ?>
        <tr>
            <td><?= htmlspecialchars($u['username']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= $u['is_admin'] ? 'Admin' : 'Utilizador' ?></td>
            <td>
                <?php if (!$u['is_admin']): ?>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="promote_id" value="<?= $u['id'] ?>">
                        <button class="primary-btn" type="submit">👑 Promover</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<hr>

<h3>📁 Categorias</h3>
<ul>
    <?php foreach ($categories as $c): ?>
        <li><?= htmlspecialchars($c['name']) ?></li>
    <?php endforeach; ?>
</ul>

<form method="post">
    <label>Nova categoria:</label>
    <input type="text" name="new_category" required>
    <button class="primary-btn" type="submit">➕ Adicionar</button>
</form>
</main>

<?php include '../includes/footer.php'; ?>

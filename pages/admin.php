<?php
require_once '../includes/auth.php';
require_once '../includes/csrf.php';
require_login();
require_once '../includes/db.php';
require_once '../includes/head.php';
require_once '../includes/header.php';

if ($_SESSION['is_admin'] != 1) {
    echo "<main class='dashboard-container'><p class='error'>❌ Acesso restrito. Apenas administradores podem aceder.</p></main>";
    include '../includes/footer.php';
    exit();
}

$message = '';

// Promoção a admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['promote_id'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) die("Token CSRF inválido.");

    $promote_id = (int) $_POST['promote_id'];
    if ($promote_id > 0) {
        $stmt = $db->prepare("UPDATE users SET is_admin = 1 WHERE id = :id");
        $stmt->execute([':id' => $promote_id]);
        $message = "✅ Utilizador promovido a admin.";
    }
}

// Aprovar/reprovar serviço
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['service_action'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) die("Token CSRF inválido.");

    $serviceId = (int) $_POST['service_id'];
    $action = $_POST['service_action'];

    if (in_array($action, ['aprovado', 'reprovado']) && $serviceId > 0) {
        $stmt = $db->prepare("UPDATE services SET status = :status WHERE id = :id");
        $stmt->execute([':status' => $action, ':id' => $serviceId]);
        $message = "✅ Serviço ID $serviceId atualizado para '$action'.";
    }
}

// Dados
$users = $db->query("SELECT id, username, email, is_admin FROM users ORDER BY username ASC")->fetchAll(PDO::FETCH_ASSOC);
$totalUsers = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalPostedServices = $db->query("SELECT COUNT(*) FROM services WHERE status = 'aprovado'")->fetchColumn();
$totalTransactions = $db->query("SELECT COUNT(*) FROM transactions")->fetchColumn();
$totalReviews = $db->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
$pendingServices = $db->query("
    SELECT s.id, s.title, u.username
    FROM services s
    JOIN users u ON s.freelancer_id = u.id
    WHERE s.status = 'pendente'
    ORDER BY s.id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="dashboard-container">
    <h2>⚙️ Painel de Administração</h2>

    <?php if (!empty($message)): ?>
        <p class="success"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <section style="margin-top: 2rem;">
        <h3>📊 Estatísticas Gerais</h3>
        <ul class="stats-list">
            <li>👤 Utilizadores registados: <strong><?= $totalUsers ?></strong></li>
            <li>🛠 Serviços ativos: <strong><?= $totalPostedServices ?></strong></li>
            <li>🛒 Serviços contratados: <strong><?= $totalTransactions ?></strong></li>
            <li>⭐ Avaliações feitas: <strong><?= $totalReviews ?></strong></li>
        </ul>
    </section>

    <hr>

    <section style="margin-top: 2rem;">
        <h3>👥 Utilizadores</h3>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="padding-right: 20px;">Username</th>
                        <th style="padding-right: 20px;">Email</th>
                        <th style="padding-right: 20px;">Tipo</th>
                        <th>Ação</th>
                    </tr>
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
        <h3>🛠 Serviços Pendentes</h3>

        <?php if (count($pendingServices) === 0): ?>
            <p>🎉 Nenhum serviço pendente no momento.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="admin-table" style="border-spacing: 0 10px; width: 100%;">
                    <thead>
                        <tr>
                            <th style="padding-right: 20px;">ID</th>
                            <th style="padding-right: 20px;">Título</th>
                            <th style="padding-right: 20px;">Freelancer</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingServices as $s): ?>
                            <tr>
                                <td><?= $s['id'] ?></td>
                                <td><?= htmlspecialchars($s['title']) ?></td>
                                <td><?= htmlspecialchars($s['username']) ?></td>
                                <td>
                                    <form method="post" class="inline-form" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                        <input type="hidden" name="service_id" value="<?= $s['id'] ?>">
                                        <button name="service_action" value="aprovado" class="primary-btn">✔️ Aprovar</button>
                                    </form>

                                    <form method="post" class="inline-form" style="display:inline;" onsubmit="return confirm('Tens a certeza que queres reprovar este serviço?');">
                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                        <input type="hidden" name="service_id" value="<?= $s['id'] ?>">
                                        <button name="service_action" value="reprovado" class="danger-btn">❌ Reprovar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include '../includes/footer.php'; ?>

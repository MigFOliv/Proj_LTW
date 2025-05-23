<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/csrf.php';
require_login();
?>

<!DOCTYPE html>
<html lang="pt">
<?php include '../includes/head.php'; ?>
<body>
<?php include '../includes/header.php'; ?>

<?php
$user_id = $_SESSION['user_id'];

$stmt = $db->prepare("
    SELECT t.*, s.title, u.username AS client_name
    FROM transactions t
    JOIN services s ON t.service_id = s.id
    JOIN users u ON t.client_id = u.id
    WHERE s.freelancer_id = :uid
    ORDER BY t.created_at DESC
");
$stmt->execute([':uid' => $user_id]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="dashboard-container">
    <h2>📥 Pedidos Recebidos</h2>

    <?php if (count($requests) === 0): ?>
        <p class="no-services">Ainda não recebeste nenhum pedido.</p>
    <?php else: ?>
        <ul class="service-list">
            <?php foreach ($requests as $req): ?>
                <li class="service-item">
                    <h4><?= htmlspecialchars($req['title']) ?></h4>
                    <p>
                        <strong>Cliente:</strong>
                        <?= htmlspecialchars($req['client_name']) ?>
                        (<a href="public_profile.php?id=<?= $req['client_id'] ?>">👤 Ver perfil</a>)
                    </p>
                    <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($req['created_at'])) ?></p>
                    <p><strong>Estado:</strong> <?= ucfirst(htmlspecialchars($req['status'])) ?></p>

                    <?php if ($req['status'] === 'pending'): ?>
                        <form method="post" action="complete_order.php" class="inline-form" onsubmit="return confirm('Confirmas que este serviço foi entregue?');">
                            <input type="hidden" name="transaction" value="<?= $req['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <button type="submit" class="primary-btn">✔️ Marcar como Entregue</button>
                        </form>
                    <?php else: ?>
                        <p class="success">✅ Entregue em <?= date('d/m/Y', strtotime($req['completed_at'])) ?></p>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>
</body>
</html>


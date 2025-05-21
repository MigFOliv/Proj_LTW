
<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/csrf.php';
require_login();
include '../includes/header.php';

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

<main>
    <h2>📥 Pedidos Recebidos</h2>

    <?php if (count($requests) === 0): ?>
        <p>Ainda não recebeste nenhum pedido.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($requests as $req): ?>
                <li class="service-item">
                    <strong><?= htmlspecialchars($req['title']) ?></strong><br>
                    <small>Cliente: <?= htmlspecialchars($req['client_name']) ?></small><br>
                    <small>Data: <?= date('d/m/Y H:i', strtotime($req['created_at'])) ?></small><br>
                    <small>Estado: <strong><?= ucfirst($req['status']) ?></strong></small><br><br>

                    <?php if ($req['status'] === 'pending'): ?>
                        <form method="post" action="complete_order.php" onsubmit="return confirm('Confirmas que este serviço foi entregue?');" style="display:inline;">
                            <input type="hidden" name="transaction" value="<?= $req['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <button type="submit" class="primary-btn">✔️ Marcar como Entregue</button>
                        </form>
                    <?php else: ?>
                        <em>✅ Entregue em <?= date('d/m/Y', strtotime($req['completed_at'])) ?></em>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>

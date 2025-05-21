
<?php
require_once '../includes/auth.php';
require_login();
require_once '../includes/db.php';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];

// Buscar serviços contratados pelo cliente
$stmt = $db->prepare("
    SELECT t.*, s.title, s.freelancer_id, u.username AS freelancer_name
    FROM transactions t
    JOIN services s ON t.service_id = s.id
    JOIN users u ON s.freelancer_id = u.id
    WHERE t.client_id = :uid
    ORDER BY t.created_at DESC
");
$stmt->execute([':uid' => $user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main>
    <h2>🛒 Meus Serviços Contratados</h2>

    <?php if (count($orders) === 0): ?>
        <p>Ainda não contrataste nenhum serviço.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($orders as $order): ?>
                <li class="service-item">
                    <strong><?= htmlspecialchars($order['title']) ?></strong><br>
                    <small>
                        Freelancer: <?= htmlspecialchars($order['freelancer_name']) ?>
                        (<a href="public_profile.php?id=<?= $order['freelancer_id'] ?>">👤 Ver perfil</a>)
                    </small><br>
                    <small>Data: <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></small><br>
                    <small>Estado: <strong><?= ucfirst($order['status']) ?></strong></small><br><br>

                    <?php if ($order['status'] === 'completed'): ?>
                        <?php
                        // Verificar se já foi avaliado
                        $check = $db->prepare("SELECT 1 FROM reviews WHERE transaction_id = :tid");
                        $check->execute([':tid' => $order['id']]);
                        $alreadyReviewed = $check->fetchColumn();
                        ?>

                        <?php if (!$alreadyReviewed): ?>
                            <a href="service_review.php?transaction=<?= $order['id'] ?>">
                                <button class="primary-btn">⭐ Avaliar Serviço</button>
                            </a>
                        <?php else: ?>
                            <p style="color: green;">✅ Já avaliado</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>

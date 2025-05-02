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
                    <small>Freelancer: <?= htmlspecialchars($order['freelancer_name']) ?></small><br>
                    <small>Data: <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></small><br>
                    <small>Estado: <strong><?= ucfirst($order['status']) ?></strong></small><br><br>

                    <?php if ($order['status'] === 'completed'): ?>
                        <a href="service_review.php?transaction=<?= $order['id'] ?>">
                            <button class="primary-btn">⭐ Avaliar Serviço</button>
                        </a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>

<?php
require_once '../includes/auth.php';
require_login();
require_once '../includes/db.php';
?>

<!DOCTYPE html>
<html lang="pt">
<?php include '../includes/head.php'; ?>
<body>
<?php include '../includes/header.php'; ?>

<?php
$user_id = $_SESSION['user_id'];

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

<main class="dashboard-container">
    <h2>🛒 Meus Serviços Contratados</h2>

    <?php if (count($orders) === 0): ?>
        <p class="no-services">Ainda não contrataste nenhum serviço.</p>
    <?php else: ?>
        <ul class="service-list">
            <?php foreach ($orders as $order): ?>
                <li class="service-item">
                    <h4><?= htmlspecialchars($order['title']) ?></h4>
                    <p>
                        <strong>Freelancer:</strong>
                        <?= htmlspecialchars($order['freelancer_name']) ?>
                        (<a href="public_profile.php?id=<?= $order['freelancer_id'] ?>">👤 Ver perfil</a>)
                    </p>
                    <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
                    <p><strong>Estado:</strong> <?= ucfirst(htmlspecialchars($order['status'])) ?></p>

                    <?php if ($order['status'] === 'completed'): ?>
                        <?php
                        $check = $db->prepare("SELECT 1 FROM reviews WHERE transaction_id = :tid");
                        $check->execute([':tid' => $order['id']]);
                        $alreadyReviewed = $check->fetchColumn();
                        ?>

                        <?php if (!$alreadyReviewed): ?>
                            <a href="service_review.php?transaction=<?= $order['id'] ?>" class="primary-btn">⭐ Avaliar Serviço</a>
                        <?php else: ?>
                            <p class="success">✅ Já avaliado</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>
</body>
</html>

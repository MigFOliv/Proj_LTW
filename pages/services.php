<?php
require_once '../includes/header.php';
require_once '../includes/db.php';

// Obter todos os serviços com nome do freelancer
$stmt = $db->query("SELECT s.*, u.username FROM services s JOIN users u ON s.freelancer_id = u.id ORDER BY s.id DESC");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>🌐 Todos os Serviços Disponíveis</h2>

<?php if (count($services) === 0): ?>
    <p>Não há serviços disponíveis de momento.</p>
<?php else: ?>
    <?php foreach ($services as $s): ?>
        <?php
        // Obter média de avaliações
        $stmtRating = $db->prepare("
            SELECT AVG(r.rating) as avg_rating
            FROM reviews r
            JOIN transactions t ON r.transaction_id = t.id
            WHERE t.service_id = :sid
        ");
        $stmtRating->execute([':sid' => $s['id']]);
        $avg = $stmtRating->fetch(PDO::FETCH_ASSOC)['avg_rating'];
        ?>
        <div class="service-item">
            <h3><?= htmlspecialchars($s['title']) ?></h3>
            <?php if ($avg !== null): ?>
                <p>⭐ <?= number_format($avg, 1) ?> / 5</p>
            <?php endif; ?>
            <p><em><?= htmlspecialchars($s['description']) ?></em></p>
            <p><strong><?= htmlspecialchars($s['price']) ?>€</strong> • Entrega: <?= htmlspecialchars($s['delivery_time']) ?></p>
            <p><small>Por <strong><?= htmlspecialchars($s['username']) ?></strong> • Categoria: <?= htmlspecialchars($s['category'] ?? '—') ?></small></p>

            <a href="service_detail.php?id=<?= $s['id'] ?>">
                <button class="primary-btn">🔍 Ver mais</button>
            </a>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>

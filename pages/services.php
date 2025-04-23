<?php
require_once '../includes/header.php';
require_once '../includes/db.php';

$stmt = $db->query("SELECT s.*, u.username FROM services s JOIN users u ON s.freelancer_id = u.id ORDER BY s.id DESC");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>🌐 Todos os Serviços Disponíveis</h2>

<?php if (count($services) === 0): ?>
    <p>Não há serviços disponíveis de momento.</p>
<?php else: ?>
    <?php foreach ($services as $s): ?>
        <div class="service-item">
            <h3><?= htmlspecialchars($s['title']) ?></h3>
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

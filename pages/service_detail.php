<?php
require_once '../includes/db.php';
require_once '../includes/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p>ID inválido.</p>";
    include '../includes/footer.php';
    exit();
}

$id = (int) $_GET['id'];

$stmt = $db->prepare("SELECT s.*, u.username, u.id AS freelancer_id FROM services s JOIN users u ON s.freelancer_id = u.id WHERE s.id = :id");
$stmt->execute([':id' => $id]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    echo "<p>Serviço não encontrado.</p>";
    include '../includes/footer.php';
    exit();
}
?>

<main>
<h2>🔍 Detalhes do Serviço</h2>

<?php if (isset($_GET['hired'])): ?>
    <p style="color: green;">✅ Serviço contratado com sucesso!</p>
<?php endif; ?>

<div class="service-item">
    <h3><?= htmlspecialchars($service['title']) ?></h3>
    <p><em><?= htmlspecialchars($service['description']) ?></em></p>
    <p><strong><?= htmlspecialchars($service['price']) ?>€</strong> • Entrega: <?= htmlspecialchars($service['delivery_time']) ?></p>
    <p><small>Por <strong><?= htmlspecialchars($service['username']) ?></strong> • Categoria: <?= htmlspecialchars($service['category']) ?></small></p>
</div>

<?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $service['freelancer_id']): ?>
    <p>
        <a href="contact_freelancer.php?to=<?= $service['freelancer_id'] ?>&service=<?= $service['id'] ?>">
            <button class="primary-btn">💬 Contactar Freelancer</button>
        </a>
    </p>
    <p>
        <a href="hire_service.php?service=<?= $service['id'] ?>">
            <button class="primary-btn">🛒 Contratar Serviço</button>
        </a>
    </p>
<?php endif; ?>

<?php
// Obter avaliações
$stmt = $db->prepare("
    SELECT r.rating, r.comment, t.completed_at, u.username
    FROM reviews r
    JOIN transactions t ON r.transaction_id = t.id
    JOIN users u ON t.client_id = u.id
    WHERE t.service_id = :sid
");
$stmt->execute([':sid' => $service['id']]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular média
$media = count($reviews) ? array_sum(array_column($reviews, 'rating')) / count($reviews) : null;
?>

<hr>
<h3>⭐ Avaliações</h3>

<?php if ($media !== null): ?>
    <p><strong>Média:</strong> <?= number_format($media, 1) ?> / 5 ⭐</p>
<?php else: ?>
    <p>Este serviço ainda não tem avaliações.</p>
<?php endif; ?>

<?php if (count($reviews) > 0): ?>
    <ul>
        <?php foreach ($reviews as $r): ?>
            <li class="service-item">
                <strong>⭐ <?= $r['rating'] ?>/5</strong> — por <em><?= htmlspecialchars($r['username']) ?></em><br>
                <small><?= date('d/m/Y', strtotime($r['completed_at'])) ?></small>
                <?php if (!empty($r['comment'])): ?>
                    <p><?= htmlspecialchars($r['comment']) ?></p>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<p><a href="services.php">⬅️ Voltar à lista</a></p>
</main>

<?php include '../includes/footer.php'; ?>

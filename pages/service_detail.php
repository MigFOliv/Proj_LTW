<?php
require_once '../includes/db.php';
require_once '../includes/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p>ID inválido.</p>";
    include '../includes/footer.php';
    exit();
}

$id = (int) $_GET['id'];

$stmt = $db->prepare("SELECT s.*, u.username FROM services s JOIN users u ON s.freelancer_id = u.id WHERE s.id = :id");
$stmt->execute([':id' => $id]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    echo "<p>Serviço não encontrado.</p>";
    include '../includes/footer.php';
    exit();
}
?>

<h2>🔍 Detalhes do Serviço</h2>

<div class="service-item">
    <h3><?= htmlspecialchars($service['title']) ?></h3>
    <p><em><?= htmlspecialchars($service['description']) ?></em></p>
    <p><strong><?= htmlspecialchars($service['price']) ?>€</strong> • Entrega: <?= htmlspecialchars($service['delivery_time']) ?></p>
    <p><small>Por <strong><?= htmlspecialchars($service['username']) ?></strong> • Categoria: <?= htmlspecialchars($service['category']) ?></small></p>
</div>

<p><a href="services.php">⬅️ Voltar à lista</a></p>

<?php include '../includes/footer.php'; ?>

<?php
require_once '../includes/db.php';
require_once '../includes/header.php';

$service_id = $_GET['id'] ?? null;

if (!$service_id) {
    echo "<p>Serviço inválido.</p>";
    include '../includes/footer.php';
    exit();
}

// Buscar o serviço com nome do freelancer
$stmt = $db->prepare("SELECT s.*, u.username, c.name AS category_name
                      FROM services s
                      JOIN users u ON s.freelancer_id = u.id
                      LEFT JOIN categories c ON s.category_id = c.id
                      WHERE s.id = :id");
$stmt->execute([':id' => $service_id]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    echo "<p>Serviço não encontrado.</p>";
    include '../includes/footer.php';
    exit();
}
?>

<h2>📝 <?= htmlspecialchars($service['title']) ?></h2>

<p><strong>Descrição:</strong><br>
<em><?= nl2br(htmlspecialchars($service['description'])) ?></em></p>

<p><strong>Preço:</strong> <?= htmlspecialchars($service['price']) ?>€</p>
<p><strong>Entrega:</strong> <?= htmlspecialchars($service['delivery_time']) ?></p>
<p><strong>Freelancer:</strong> <?= htmlspecialchars($service['username']) ?></p>
<p><strong>Categoria:</strong> <?= htmlspecialchars($service['category_name'] ?? '—') ?></p>

<?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $service['freelancer_id']): ?>
    <p>
        <a href="contact_freelancer.php?to=<?= $service['freelancer_id'] ?>&service=<?= $service['id'] ?>">
            <button class="primary-btn">💬 Contactar Freelancer</button>
        </a>
    </p>
<?php endif; ?>

<p><a href="services.php">⬅️ Voltar aos serviços</a></p>

<?php include '../includes/footer.php'; ?>

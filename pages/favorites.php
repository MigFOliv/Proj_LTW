<?php
require_once '../includes/auth.php';
require_login();
require_once '../includes/db.php';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];

// Serviços favoritos do utilizador
$stmt = $db->prepare("
    SELECT s.*, u.username
    FROM favorites f
    JOIN services s ON f.service_id = s.id
    JOIN users u ON s.freelancer_id = u.id
    WHERE f.user_id = :uid
    ORDER BY s.id DESC
");
$stmt->execute([':uid' => $user_id]);
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main>
<h2>⭐ Serviços Favoritos</h2>

<?php if (count($favorites) === 0): ?>
    <p>Não tens serviços favoritos de momento.</p>
<?php else: ?>
    <?php foreach ($favorites as $s): ?>
        <div class="service-item">
            <?php if (!empty($s['media_path']) && file_exists($s['media_path'])): ?>
                <img src="<?= htmlspecialchars($s['media_path']) ?>" alt="Imagem do serviço" style="max-width: 100%; margin-bottom: 10px;">
            <?php endif; ?>

            <h3><?= htmlspecialchars($s['title']) ?></h3>
            <p><em><?= htmlspecialchars($s['description']) ?></em></p>
            <p><strong><?= htmlspecialchars($s['price']) ?>€</strong> • Entrega: <?= htmlspecialchars($s['delivery_time']) ?></p>
            <p><small>Por <strong><?= htmlspecialchars($s['username']) ?></strong> • Categoria: <?= htmlspecialchars($s['category'] ?? '—') ?></small></p>

            <form method="post" action="toggle_favorite.php" style="display:inline;">
                <input type="hidden" name="service_id" value="<?= $s['id'] ?>">
                <button type="submit" class="danger-btn">❌ Remover dos Favoritos</button>
            </form>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>

<?php
require_once '../includes/db.php';
require_once '../includes/csrf.php';
require_once '../includes/head.php';
require_once '../includes/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<main class='dashboard-container'><p>ID inválido.</p></main>";
    include '../includes/footer.php';
    exit();
}

$id = (int) $_GET['id'];

$stmt = $db->prepare("SELECT s.*, u.username, u.id AS freelancer_id FROM services s JOIN users u ON s.freelancer_id = u.id WHERE s.id = :id");
$stmt->execute([':id' => $id]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    echo "<main class='dashboard-container'><p>Serviço não encontrado.</p></main>";
    include '../includes/footer.php';
    exit();
}


function getCurrencySymbol($currency) {
    return match (strtoupper($currency)) {
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'BRL' => 'R$',
        'JPY' => '¥',
        default => $currency
    };
}

$isFavorite = false;
if (isset($_SESSION['user_id'])) {
    $checkFav = $db->prepare("SELECT 1 FROM favorites WHERE user_id = :uid AND service_id = :sid");
    $checkFav->execute([
        ':uid' => $_SESSION['user_id'],
        ':sid' => $service['id']
    ]);
    $isFavorite = $checkFav->fetchColumn();
}
?>

<main class="dashboard-container">
    <h2>🔍 Detalhes do Serviço</h2>

    <?php if (isset($_GET['hired'])): ?>
        <p class="success">✅ Serviço contratado com sucesso!</p>
    <?php endif; ?>

    <div class="service-item">
        <?php
        $imgPath = '../' . $service['media_path'];
        if (!empty($service['media_path']) && file_exists($imgPath)): ?>
            <img src="/<?= htmlspecialchars($service['media_path']) ?>" alt="Imagem do serviço" class="service-image">
        <?php endif; ?>

        <h4><?= htmlspecialchars($service['title']) ?></h4>
        <p class="description"><?= nl2br(htmlspecialchars($service['description'])) ?></p>
        <p>
            <strong>
                <?= getCurrencySymbol($service['currency'] ?? '') . number_format($service['price'], 2) ?>
            </strong>
            • Entrega: <?= htmlspecialchars($service['delivery_time']) ?>
        </p>
        <p><small>
            Por <strong><?= htmlspecialchars($service['username']) ?></strong>
            (<a href="public_profile.php?id=<?= $service['freelancer_id'] ?>">👤 Ver perfil</a>)
            • Categoria: <?= htmlspecialchars($service['category'] ?? '—') ?>
        </small></p>
    </div>

    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $service['freelancer_id']): ?>
        <div class="service-actions">
            <a href="contact_freelancer.php?to=<?= $service['freelancer_id'] ?>&service=<?= $service['id'] ?>" class="primary-btn">💬 Contactar Freelancer</a>

            <form action="hire_service.php" method="post" class="inline-form">
                <input type="hidden" name="service_id" value="<?= $service['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                <button type="submit" class="primary-btn">🛒 Contratar Serviço</button>
            </form>

            <form action="toggle_favorite.php" method="post" class="inline-form">
                <input type="hidden" name="service_id" value="<?= $service['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                <button type="submit" class="primary-btn">
                    <?= $isFavorite ? '💔 Remover dos Favoritos' : '❤️ Adicionar aos Favoritos' ?>
                </button>
            </form>
        </div>
    <?php endif; ?>

    <hr>
    <h3>⭐ Avaliações</h3>

    <?php
    $stmt = $db->prepare("
        SELECT r.rating, r.comment, t.completed_at, u.username
        FROM reviews r
        JOIN transactions t ON r.transaction_id = t.id
        JOIN users u ON t.client_id = u.id
        WHERE t.service_id = :sid
    ");
    $stmt->execute([':sid' => $service['id']]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $media = count($reviews) ? array_sum(array_column($reviews, 'rating')) / count($reviews) : null;
    ?>

    <?php if ($media !== null): ?>
        <p><strong>Média:</strong> <?= number_format($media, 1) ?> / 5 ⭐</p>
    <?php else: ?>
        <p>Este serviço ainda não tem avaliações.</p>
    <?php endif; ?>

    <?php if (count($reviews) > 0): ?>
        <ul class="service-list">
            <?php foreach ($reviews as $r): ?>
                <li class="service-item">
                    <strong>⭐ <?= $r['rating'] ?>/5</strong> — por <em><?= htmlspecialchars($r['username']) ?></em><br>
                    <small><?= date('d/m/Y', strtotime($r['completed_at'])) ?></small>
                    <?php if (!empty($r['comment'])): ?>
                        <p><?= nl2br(htmlspecialchars($r['comment'])) ?></p>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <div class="dashboard-actions">
        <a href="services.php" class="primary-btn">⬅️ Voltar à lista</a>
    </div>
</main>

<?php include '../includes/footer.php'; ?>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/csrf.php';
require_login();
?>

<!DOCTYPE html>
<html lang="pt">
<?php include '../includes/head.php'; ?>
<body>
<?php include '../includes/header.php'; ?>

<?php
$stmt = $db->prepare("
    SELECT *
    FROM services
    WHERE freelancer_id = :id
    ORDER BY id DESC
");
$stmt->execute([':id' => $_SESSION['user_id']]);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

function renderStatusBadge($statusRaw) {
    $status = strtolower($statusRaw ?? 'pendente');
    return match ($status) {
        'aprovado' => "<span style='color: green;'>✅ Aprovado</span>",
        'reprovado' => "<span style='color: red;'>❌ Reprovado</span>",
        default => "<span style='color: orange;'>⏳ Pendente</span>"
    };
}
?>

<main class="dashboard-container">
    <h2>🎯 Painel do Freelancer</h2>
    <p>Olá, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>! Aqui estão os teus serviços.</p>

    <div class="dashboard-actions">
        <a href="add_service.php" class="primary-btn">➕ Adicionar novo serviço</a>
        <a href="stats.php" class="secondary-btn">📊 Ver Estatísticas</a>
    </div>

    <?php if (count($services) === 0): ?>
        <p class="no-services">Ainda não criaste nenhum serviço.</p>
    <?php else: ?>
        <ul class="service-list">
            <?php foreach ($services as $service): ?>
                <li class="service-item">
                    <?php
                    $imagePath = '../' . $service['media_path'];
                    if (!empty($service['media_path']) && file_exists($imagePath)):
                    ?>
                        <img src="/<?= htmlspecialchars($service['media_path']) ?>" alt="Imagem do serviço" class="service-image">
                    <?php endif; ?>

                    <h4>
                        <?= htmlspecialchars($service['title']) ?>
                        <?= $service['is_promoted'] ? '<span title="Promovido">⭐</span>' : '' ?>
                        <?= renderStatusBadge($service['status'] ?? null) ?>
                    </h4>

                    <p>
                        <strong><?= getCurrencySymbol($service['currency'] ?? '') . number_format($service['price'], 2) ?></strong>
                        • Entrega: <?= htmlspecialchars($service['delivery_time']) ?>
                    </p>

                    <p class="description"><?= htmlspecialchars($service['description']) ?></p>

                    <p>
                        <strong>Categoria:</strong> <?= htmlspecialchars($service['category'] ?: '—') ?>
                    </p>

                    <div class="service-actions">
                        <a href="edit_service.php?id=<?= $service['id'] ?>" class="primary-btn">✏️ Editar</a>

                        <form method="post" action="delete_service.php" class="inline-form" onsubmit="return confirm('Tens a certeza que queres apagar este serviço?');">
                            <input type="hidden" name="id" value="<?= $service['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <button class="primary-btn danger-btn" type="submit">❌ Apagar</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>
</body>
</html>

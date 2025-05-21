<?php
require_once '../includes/db.php';
include '../includes/header.php';

// Buscar até 6 serviços promovidos
$stmt = $db->prepare("
    SELECT s.*, u.username
    FROM services s
    JOIN users u ON s.freelancer_id = u.id
    WHERE s.is_promoted = 1
    ORDER BY s.id DESC
    LIMIT 6
");
$stmt->execute();
$featured = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main>
    <section class="hero">
        <h2>Bem-vindo à Plataforma de Freelancers</h2>
        <p>Encontra o talento certo ou oferece os teus serviços ao mundo digital.</p>
        <a href="services.php" class="primary-btn">Explorar Serviços</a>
    </section>

    <section class="featured-services">
        <h3>✨ Serviços em Destaque</h3>

        <?php if (count($featured) === 0): ?>
            <p>Sem serviços promovidos no momento. <a href="services.php">Explora todos</a>.</p>
        <?php else: ?>
            <div class="card-grid">
                <?php foreach ($featured as $s): ?>
                    <div class="service-card">
                        <?php if (!empty($s['media_path']) && file_exists('../' . $s['media_path'])): ?>
                            <img src="/<?= htmlspecialchars($s['media_path']) ?>" alt="Imagem do serviço" style="width:100%; max-height:150px; object-fit:cover; border-radius: 8px;">
                        <?php endif; ?>

                        <h4><?= htmlspecialchars($s['title']) ?></h4>
                        <p><strong>por:</strong> <?= htmlspecialchars($s['username']) ?></p>
                        <p><strong>Preço:</strong> €<?= number_format($s['price'], 2) ?></p>

                        <a href="service_detail.php?id=<?= $s['id'] ?>">
                            <button class="primary-btn">🔍 Ver mais</button>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <p class="see-all"><a href="services.php">🔎 Ver todos os serviços</a></p>
        <?php endif; ?>
    </section>
</main>

<?php include '../includes/footer.php'; ?>


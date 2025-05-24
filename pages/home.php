<?php
require_once '../includes/db.php';
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FreeLanceX</title>
  <link rel="stylesheet" href="/css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include '../includes/header.php'; ?>

<?php
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
    <div class="hero-content">
      <h2>Bem-vindo à <span style="color: #fff;">FreeLanceX</span></h2>
      <p>Encontra o talento certo ou oferece os teus serviços ao mundo digital.</p>
      <a href="/pages/services.php" class="primary-btn">Explorar Serviços</a>
    </div>
  </section>

  <section class="featured-services">
    <h3>✨ Serviços em Destaque</h3>

    <?php if (count($featured) === 0): ?>
      <p>Sem serviços promovidos no momento. <a href="/pages/services.php">Explora todos</a>.</p>
    <?php else: ?>
      <div class="card-grid">
        <?php foreach ($featured as $s): ?>
          <div class="service-card">
            <?php if (!empty($s['media_path']) && file_exists('../' . $s['media_path'])): ?>
              <img src="/<?= htmlspecialchars($s['media_path']) ?>" alt="Imagem do serviço" class="card-image">
            <?php endif; ?>

            <h4><?= htmlspecialchars($s['title']) ?></h4>
            <p><strong>por:</strong> <?= htmlspecialchars($s['username']) ?></p>
            <p><strong>Preço:</strong> €<?= number_format($s['price'], 2) ?></p>

            <a href="/pages/service_detail.php?id=<?= $s['id'] ?>" class="primary-btn">🔍 Ver mais</a>
          </div>
        <?php endforeach; ?>
      </div>
      <p class="see-all"><a href="/pages/services.php">🔎 Ver todos os serviços</a></p>
    <?php endif; ?>
  </section>
</main>

<?php include '../includes/footer.php'; ?>
</body>
</html>

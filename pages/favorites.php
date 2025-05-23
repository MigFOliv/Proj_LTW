<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/csrf.php';
require_login();

$user_id = $_SESSION['user_id'];

// Obter serviços favoritos
$stmt = $db->prepare("
    SELECT s.*, u.username, u.id AS freelancer_id
    FROM favorites f
    JOIN services s ON f.service_id = s.id
    JOIN users u ON s.freelancer_id = u.id
    WHERE f.user_id = :uid
    ORDER BY s.id DESC
");
$stmt->execute([':uid' => $user_id]);
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">
<?php include '../includes/head.php'; ?>
<body>
<?php include '../includes/header.php'; ?>

<main class="dashboard-container">
  <h2>⭐ Serviços Favoritos</h2>

  <?php if (count($favorites) === 0): ?>
    <p class="no-services">Não tens serviços favoritos de momento.</p>
  <?php else: ?>
    <ul class="service-list">
      <?php foreach ($favorites as $s): ?>
        <li class="service-item">
          <?php
            $imgPath = '../' . $s['media_path'];
            if (!empty($s['media_path']) && file_exists($imgPath)):
          ?>
            <img src="/<?= htmlspecialchars($s['media_path']) ?>" alt="Imagem do serviço" class="service-image">
          <?php endif; ?>

          <h4><?= htmlspecialchars($s['title']) ?></h4>
          <p class="description"><?= htmlspecialchars($s['description']) ?></p>
          <p><strong><?= htmlspecialchars($s['price']) ?>€</strong> • Entrega: <?= htmlspecialchars($s['delivery_time']) ?></p>
          <p><small>
            Por <strong><?= htmlspecialchars($s['username']) ?></strong>
            (<a href="public_profile.php?id=<?= $s['freelancer_id'] ?>">👤 Ver perfil</a>)
            • Categoria: <?= htmlspecialchars($s['category'] ?? '—') ?>
          </small></p>

          <div class="service-actions">
            <form method="post" action="toggle_favorite.php" class="inline-form" onsubmit="return confirm('Remover dos favoritos?');">
              <input type="hidden" name="service_id" value="<?= $s['id'] ?>">
              <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
              <button type="submit" class="danger-btn">❌ Remover dos Favoritos</button>
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


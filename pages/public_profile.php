<?php
require_once '../includes/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<main class='dashboard-container'><p>ID de utilizador inválido.</p></main>";
    exit();
}

$user_id = (int) $_GET['id'];

$stmt = $db->prepare("
    SELECT u.username, p.name, p.bio, p.profile_image
    FROM users u
    LEFT JOIN profiles p ON u.id = p.user_id
    WHERE u.id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<main class='dashboard-container'><p>Perfil não encontrado.</p></main>";
    exit();
}

$stmt = $db->prepare("SELECT id, title, price FROM services WHERE freelancer_id = ? AND status = 'aprovado'");
$stmt->execute([$user_id]);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

$imgPath = !empty($user['profile_image']) && file_exists('../' . $user['profile_image'])
    ? '/' . htmlspecialchars($user['profile_image'])
    : '/uploads/profiles/default_profile.png';
?>

<!DOCTYPE html>
<html lang="pt">
<?php include '../includes/head.php'; ?>
<body>
<?php include '../includes/header.php'; ?>

<main class="dashboard-container">
    <h2>👤 Perfil Público</h2>

    <img src="<?= $imgPath ?>" alt="Foto de perfil" class="profile-picture" width="150" style="border-radius: 50%; margin-bottom: 1rem;">

    <h3><?= htmlspecialchars($user['name'] ?? $user['username']) ?></h3>
    <?php if (!empty($user['bio'])): ?>
        <p style="max-width: 600px; margin-bottom: 1rem;"><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
    <?php endif; ?>

    <hr style="margin: 2rem 0;">
    <h4>🛠 Serviços publicados</h4>

    <?php if (count($services) === 0): ?>
        <p class="no-services">Este utilizador ainda não publicou nenhum serviço.</p>
    <?php else: ?>
        <ul class="service-list">
            <?php foreach ($services as $s): ?>
                <li>
                    <a href="service_detail.php?id=<?= $s['id'] ?>">
                        <?= htmlspecialchars($s['title']) ?> — €<?= number_format($s['price'], 2) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <p style="margin-top: 2rem;">
        <a href="services.php" class="primary-btn">⬅️ Voltar aos Serviços</a>
    </p>
</main>

<?php include '../includes/footer.php'; ?>
</body>
</html>

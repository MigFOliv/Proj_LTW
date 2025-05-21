<?php
require_once '../includes/db.php';
require_once '../includes/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<main><p>ID de utilizador inválido.</p></main>";
    include '../includes/footer.php';
    exit();
}

$user_id = (int) $_GET['id'];

// Buscar dados do perfil
$stmt = $db->prepare("
    SELECT u.username, p.name, p.bio, p.profile_image
    FROM users u
    LEFT JOIN profiles p ON u.id = p.user_id
    WHERE u.id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<main><p>Perfil não encontrado.</p></main>";
    include '../includes/footer.php';
    exit();
}

// Buscar serviços do freelancer
$stmt = $db->prepare("SELECT id, title, price FROM services WHERE freelancer_id = ?");
$stmt->execute([$user_id]);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main>
    <h2>👤 Perfil Público</h2>

    <?php
    $imgPath = !empty($user['profile_image']) && file_exists('../' . $user['profile_image'])
        ? '/' . htmlspecialchars($user['profile_image'])
        : '/uploads/profiles/default_profile.png';
    ?>

    <img src="<?= $imgPath ?>" alt="Foto de perfil" width="150" style="border-radius: 50%; margin-bottom: 1rem;">

    <h3><?= htmlspecialchars($user['name'] ?? $user['username']) ?></h3>
    <p><?= nl2br(htmlspecialchars($user['bio'] ?? '')) ?></p>

    <hr>
    <h4>🛠 Serviços publicados</h4>

    <?php if (count($services) === 0): ?>
        <p>Este utilizador ainda não publicou nenhum serviço.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($services as $s): ?>
                <li>
                    <a href="service_detail.php?id=<?= $s['id'] ?>">
                        <?= htmlspecialchars($s['title']) ?> — €<?= number_format($s['price'], 2) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <p><a href="services.php">⬅️ Voltar</a></p>
</main>

<?php include '../includes/footer.php'; ?>

<?php
require_once(__DIR__ . '/../includes/db.php');
require_once(__DIR__ . '/../includes/auth.php');
require_once(__DIR__ . '/../includes/csrf.php');
require_login();

$user_id = $_SESSION['user_id'];


$stmt = $db->prepare('
    SELECT u.username, u.email, p.name, p.bio, p.profile_image
    FROM users u
    LEFT JOIN profiles p ON u.id = p.user_id
    WHERE u.id = ?
');
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die("Token CSRF inválido.");
    }

    $name = trim($_POST['name'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $image_path = $user['profile_image'] ?? null;

    if (strlen($name) > 100) $name = substr($name, 0, 100);
    if (strlen($bio) > 1000) $bio = substr($bio, 0, 1000);

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['profile_image']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($ext, $allowed)) {
            $filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
            $destination = __DIR__ . '/../uploads/profiles/' . $filename;
            if (move_uploaded_file($tmp_name, $destination)) {
                $image_path = 'uploads/profiles/' . $filename;
            }
        }
    }

    $stmt = $db->prepare('
        INSERT INTO profiles (user_id, name, bio, profile_image)
        VALUES (?, ?, ?, ?)
        ON CONFLICT(user_id) DO UPDATE SET
            name = excluded.name,
            bio = excluded.bio,
            profile_image = excluded.profile_image
    ');
    $stmt->execute([$user_id, $name, $bio, $image_path]);

    header('Location: profile.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt">
<?php include(__DIR__ . '/../includes/head.php'); ?>
<body>
<?php include(__DIR__ . '/../includes/header.php'); ?>

<main class="dashboard-container">
    <h2>👤 Meu Perfil</h2>

    <?php
    $profileImg = !empty($user['profile_image'])
        ? htmlspecialchars($user['profile_image'])
        : 'uploads/profiles/default_profile.png';
    ?>
    <img src="/<?= $profileImg ?>" alt="Foto de Perfil" class="profile-picture" width="150" style="border-radius: 50%; margin-bottom: 1rem;">

    <p>
        <a href="public_profile.php?id=<?= $_SESSION['user_id'] ?>" class="primary-btn">
            👁 Ver meu perfil público
        </a>
    </p>

    <form method="post" enctype="multipart/form-data" class="auth-form" style="max-width: 500px;">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

        <label>Nome:
            <input type="text" name="name" maxlength="100" value="<?= htmlspecialchars($user['name'] ?? '') ?>">
        </label>

        <label>Biografia:
            <textarea name="bio" maxlength="1000" rows="4"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
        </label>

        <label>Foto de perfil:
            <input type="file" name="profile_image" accept="image/*">
        </label>

        <button type="submit" class="primary-btn">💾 Guardar Alterações</button>
    </form>

    <h3 style="margin-top: 2rem;">📧 Informações da Conta</h3>
    <ul class="stats-list">
        <li><strong>Nome:</strong> <?= htmlspecialchars($user['username']) ?></li>
        <li><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></li>
    </ul>

    <h3 style="margin-top: 2rem;">💼 Meus Serviços</h3>
    <ul class="service-list">
        <?php
        $stmt = $db->prepare('SELECT id, title, price FROM services WHERE freelancer_id = ? AND status = "aprovado"');
        $stmt->execute([$user_id]);
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($services):
            foreach ($services as $service): ?>
                <li>
                    <a href="service_detail.php?id=<?= $service['id'] ?>">
                        <?= htmlspecialchars($service['title']) ?> - €<?= number_format($service['price'], 2) ?>
                    </a>
                </li>
            <?php endforeach;
        else:
            echo '<li class="no-services">Ainda não publicaste serviços aprovados.</li>';
        endif;
        ?>
    </ul>
</main>

<?php include(__DIR__ . '/../includes/footer.php'); ?>
</body>
</html>

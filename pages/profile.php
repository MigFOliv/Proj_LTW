<?php
require_once(__DIR__ . '/../includes/db.php');
require_once(__DIR__ . '/../includes/auth.php');
require_login();

$user_id = $_SESSION['user_id'];

// Procurar dados do utilizador
$stmt = $db->prepare('
    SELECT u.username, u.email, p.name, p.bio, p.profile_image
    FROM users u
    LEFT JOIN profiles p ON u.id = p.user_id
    WHERE u.id = ?
');
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Atualizar perfil (formulário)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $bio = $_POST['bio'] ?? '';
    $image_path = $user['profile_image'] ?? null;

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['profile_image']['tmp_name'];
        $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
        $destination = __DIR__ . '/../uploads/profiles/' . $filename;

        // Move o ficheiro
        if (move_uploaded_file($tmp_name, $destination)) {
            $image_path = 'uploads/profiles/' . $filename;
        }
    }

    // Atualiza ou insere novo perfil
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

<?php include(__DIR__ . '/../includes/header.php'); ?>

<?php
    $profileImg = !empty($user['profile_image']) 
        ? htmlspecialchars($user['profile_image']) 
        : 'uploads/profiles/default_profile.png';
?>
<img src="/<?= $profileImg ?>" alt="Foto de Perfil" width="150" style="border-radius: 8px;">

<h2>Perfil</h2>

<form method="post" enctype="multipart/form-data">
    <label>Nome:</label>
    <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>">

    <label>Biografia:</label>
    <textarea name="bio"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>

    <label>Foto de perfil:</label>
<input type="file" name="profile_image" accept="image/*">

    <button type="submit">Guardar Alterações</button>
</form>

<h3>Informações da Conta</h3>
<ul>
    <li><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></li>
    <li><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></li>
</ul>

<h3>Meus Serviços</h3>
<ul>
<?php
$stmt = $db->prepare('SELECT id, title, price FROM services WHERE freelancer_id = ?');
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
    echo '<li>Não tens serviços publicados.</li>';
endif;
?>
</ul>

<?php include(__DIR__ . '/../includes/footer.php'); ?>

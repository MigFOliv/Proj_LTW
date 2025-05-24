<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/csrf.php';
require_once '../includes/head.php';
require_once '../includes/header.php';
require_login();

$user_id = $_SESSION['user_id'];
$service_id = $_POST['service_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<main class='dashboard-container'><p class='error'>❌ Requisição inválida.</p></main>";
    include '../includes/footer.php';
    exit();
}

if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    echo "<main class='dashboard-container'><p class='error'>❌ Token CSRF inválido.</p></main>";
    include '../includes/footer.php';
    exit();
}

if (!$service_id || !is_numeric($service_id)) {
    echo "<main class='dashboard-container'><p class='error'>❌ Serviço inválido.</p></main>";
    include '../includes/footer.php';
    exit();
}

$stmt = $db->prepare("SELECT 1 FROM favorites WHERE user_id = :uid AND service_id = :sid");
$stmt->execute([
    ':uid' => $user_id,
    ':sid' => $service_id
]);
$isFavorite = $stmt->fetchColumn();

if ($isFavorite) {
    $action = 'removido';
    $remove = $db->prepare("DELETE FROM favorites WHERE user_id = :uid AND service_id = :sid");
    $remove->execute([
        ':uid' => $user_id,
        ':sid' => $service_id
    ]);
} else {
    $action = 'adicionado';
    $add = $db->prepare("INSERT INTO favorites (user_id, service_id) VALUES (:uid, :sid)");
    $add->execute([
        ':uid' => $user_id,
        ':sid' => $service_id
    ]);
}
?>

<main class="dashboard-container">
    <p class="success">✅ Serviço <?= $action ?> aos favoritos com sucesso.</p>
    <div class="dashboard-actions">
        <a href="service_detail.php?id=<?= htmlspecialchars($service_id) ?>" class="primary-btn">⬅️ Voltar ao Serviço</a>
    </div>
</main>

<?php include '../includes/footer.php'; ?>

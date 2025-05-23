<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/csrf.php';
require_once '../includes/head.php';
require_once '../includes/header.php';

require_login();

$user_id = $_SESSION['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<main class='dashboard-container'><p class='error'>❌ Requisição inválida.</p></main>";
    include '../includes/footer.php';
    exit();
}

$service_id = $_POST['service_id'] ?? null;

// Verifica CSRF
if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    echo "<main class='dashboard-container'><p class='error'>❌ Token CSRF inválido.</p></main>";
    include '../includes/footer.php';
    exit();
}

// Validação
if (!$service_id || !is_numeric($service_id)) {
    echo "<main class='dashboard-container'><p class='error'>❌ Serviço inválido.</p></main>";
    include '../includes/footer.php';
    exit();
}

// Verifica se o serviço existe
$stmt = $db->prepare("SELECT * FROM services WHERE id = :id");
$stmt->execute([':id' => $service_id]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    echo "<main class='dashboard-container'><p class='error'>❌ Serviço não encontrado.</p></main>";
    include '../includes/footer.php';
    exit();
}

// Impede contratar o próprio serviço
if ($service['freelancer_id'] == $user_id) {
    echo "<main class='dashboard-container'><p class='error'>❌ Não podes contratar o teu próprio serviço.</p></main>";
    include '../includes/footer.php';
    exit();
}

// Verifica se já há transação pendente
$check = $db->prepare("
    SELECT id FROM transactions
    WHERE client_id = :cid AND service_id = :sid AND status = 'pending'
");
$check->execute([':cid' => $user_id, ':sid' => $service_id]);

if (!$check->fetch()) {
    // Cria nova transação
    $stmt = $db->prepare("INSERT INTO transactions (client_id, service_id) VALUES (:cid, :sid)");
    $stmt->execute([':cid' => $user_id, ':sid' => $service_id]);
}
?>

<main class="dashboard-container">
    <p class="success">✅ Serviço contratado com sucesso!</p>
    <div class="dashboard-actions">
        <a href="service_detail.php?id=<?= htmlspecialchars($service_id) ?>" class="primary-btn">⬅️ Voltar ao Serviço</a>
    </div>
</main>

<?php include '../includes/footer.php'; ?>

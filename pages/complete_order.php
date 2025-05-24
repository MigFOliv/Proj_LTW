<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/csrf.php';
require_once '../includes/head.php';
require_once '../includes/header.php';
require_login();

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

$user_id = $_SESSION['user_id'];
$transaction_id = $_POST['transaction'] ?? null;

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

if (!$transaction_id || !is_numeric($transaction_id)) {
    echo "<main class='dashboard-container'><p class='error'>❌ Pedido inválido.</p></main>";
    include '../includes/footer.php';
    exit();
}

$stmt = $db->prepare("
    SELECT t.*, s.freelancer_id
    FROM transactions t
    JOIN services s ON t.service_id = s.id
    WHERE t.id = :tid
");
$stmt->execute([':tid' => $transaction_id]);
$transaction = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaction || $transaction['freelancer_id'] != $user_id) {
    echo "<main class='dashboard-container'><p class='error'>❌ Acesso não autorizado.</p></main>";
    include '../includes/footer.php';
    exit();
}

$update = $db->prepare("
    UPDATE transactions
    SET status = 'completed', completed_at = CURRENT_TIMESTAMP
    WHERE id = :tid
");
$update->execute([':tid' => $transaction_id]);
?>

<main class="dashboard-container">
    <p class="success">
        ✅ Pedido no valor de 
        <strong><?= getCurrencySymbol($transaction['currency']) . number_format($transaction['amount'], 2) ?></strong> 
        marcado como concluído com sucesso.
    </p>
    <div class="dashboard-actions">
        <a href="my_requests.php" class="primary-btn">⬅️ Voltar aos Pedidos</a>
    </div>
</main>

<?php include '../includes/footer.php'; ?>

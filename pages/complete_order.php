<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/csrf.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Requisição inválida.");
}

if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    die("Token CSRF inválido.");
}

$user_id = $_SESSION['user_id'];
$transaction_id = $_POST['transaction'] ?? null;

if (!$transaction_id || !is_numeric($transaction_id)) {
    die("Pedido inválido.");
}

// Verifica se a transação pertence a um serviço do freelancer autenticado
$stmt = $db->prepare("
    SELECT t.*, s.freelancer_id
    FROM transactions t
    JOIN services s ON t.service_id = s.id
    WHERE t.id = :tid
");
$stmt->execute([':tid' => $transaction_id]);
$transaction = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaction || $transaction['freelancer_id'] != $user_id) {
    die("Acesso não autorizado.");
}

// Atualizar o estado para 'completed'
$update = $db->prepare("
    UPDATE transactions
    SET status = 'completed', completed_at = CURRENT_TIMESTAMP
    WHERE id = :tid
");
$update->execute([':tid' => $transaction_id]);

header("Location: my_requests.php");
exit();

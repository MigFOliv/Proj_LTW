<?php
require_once '../includes/auth.php';
require_login();
require_once '../includes/db.php';

$user_id = $_SESSION['user_id'];

if (!isset($_GET['transaction']) || !is_numeric($_GET['transaction'])) {
    die("Pedido inválido.");
}

$transaction_id = (int) $_GET['transaction'];

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

<?php
require_once '../includes/auth.php';
require_login();
require_once '../includes/db.php';

$service_id = $_GET['id'] ?? null;

// Verifica se o ID é válido
if (!$service_id) {
    die("ID inválido.");
}

// Verifica se o serviço pertence ao utilizador logado
$stmt = $db->prepare("SELECT * FROM services WHERE id = :id AND freelancer_id = :uid");
$stmt->execute([
    ':id' => $service_id,
    ':uid' => $_SESSION['user_id']
]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    die("Serviço não encontrado ou não tens permissão para o apagar.");
}

// Apaga o serviço
$delete = $db->prepare("DELETE FROM services WHERE id = :id AND freelancer_id = :uid");
$delete->execute([
    ':id' => $service_id,
    ':uid' => $_SESSION['user_id']
]);

// Redireciona de volta para o dashboard
header("Location: dashboard.php");
exit();

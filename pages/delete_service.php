<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/csrf.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Requisição inválida.");
}

$service_id = $_POST['id'] ?? null;

// Verifica CSRF
if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    die("Token CSRF inválido.");
}

// Verifica se o ID é válido
if (!$service_id || !is_numeric($service_id)) {
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

header("Location: dashboard.php");
exit();

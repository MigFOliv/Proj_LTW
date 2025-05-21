<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/csrf.php';
require_login();

// Garantir que é um pedido POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Requisição inválida.");
}

// Validar token CSRF
if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    die("Token CSRF inválido.");
}

$user_id = $_SESSION['user_id'];
$service_id = $_POST['service_id'] ?? null;

if (!$service_id || !is_numeric($service_id)) {
    header("Location: services.php");
    exit();
}

// Verificar se já está nos favoritos
$stmt = $db->prepare("SELECT 1 FROM favorites WHERE user_id = :uid AND service_id = :sid");
$stmt->execute([
    ':uid' => $user_id,
    ':sid' => $service_id
]);
$isFavorite = $stmt->fetchColumn();

if ($isFavorite) {
    // Remover dos favoritos
    $remove = $db->prepare("DELETE FROM favorites WHERE user_id = :uid AND service_id = :sid");
    $remove->execute([
        ':uid' => $user_id,
        ':sid' => $service_id
    ]);
} else {
    // Adicionar aos favoritos
    $add = $db->prepare("INSERT INTO favorites (user_id, service_id) VALUES (:uid, :sid)");
    $add->execute([
        ':uid' => $user_id,
        ':sid' => $service_id
    ]);
}

// Redirecionar para a página anterior
header("Location: service_detail.php?id=" . $service_id);
exit();

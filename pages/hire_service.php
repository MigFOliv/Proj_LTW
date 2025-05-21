<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/csrf.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Requisição inválida.");
}

$service_id = $_POST['service_id'] ?? null;
$client_id = $_SESSION['user_id'] ?? null;

// Verifica CSRF
if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    die("Token CSRF inválido.");
}

// Validação do ID
if (!$service_id || !is_numeric($service_id)) {
    die("Serviço inválido.");
}

$service_id = (int)$service_id;

// Verifica se o serviço existe
$stmt = $db->prepare("SELECT * FROM services WHERE id = :id");
$stmt->execute([':id' => $service_id]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    die("Serviço não encontrado.");
}

// Impede o freelancer de contratar o próprio serviço
if ($service['freelancer_id'] == $client_id) {
    die("Não podes contratar o teu próprio serviço.");
}

// Verifica se já existe uma transação pendente
$check = $db->prepare("
    SELECT id FROM transactions
    WHERE client_id = :cid AND service_id = :sid AND status = 'pending'
");
$check->execute([':cid' => $client_id, ':sid' => $service_id]);

if ($check->fetch()) {
    header("Location: service_detail.php?id=$service_id&hired=1");
    exit();
}

// Cria nova transação
$stmt = $db->prepare("
    INSERT INTO transactions (client_id, service_id)
    VALUES (:cid, :sid)
");
$stmt->execute([
    ':cid' => $client_id,
    ':sid' => $service_id
]);

header("Location: service_detail.php?id=$service_id&hired=1");
exit();

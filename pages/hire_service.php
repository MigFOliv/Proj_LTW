<?php
require_once '../includes/auth.php';
require_login();
require_once '../includes/db.php';

if (!isset($_GET['service']) || !is_numeric($_GET['service'])) {
    die("Serviço inválido.");
}

$service_id = (int) $_GET['service'];
$client_id = $_SESSION['user_id'];

// Verifica se o serviço existe
$stmt = $db->prepare("SELECT * FROM services WHERE id = :id");
$stmt->execute([':id' => $service_id]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    die("Serviço não encontrado.");
}

// Impede o próprio freelancer de contratar seu serviço
if ($service['freelancer_id'] == $client_id) {
    die("Não podes contratar o teu próprio serviço.");
}

// Verifica se já existe uma transação pendente para este serviço por este cliente
$check = $db->prepare("
    SELECT * FROM transactions
    WHERE client_id = :cid AND service_id = :sid AND status = 'pending'
");
$check->execute([':cid' => $client_id, ':sid' => $service_id]);

if ($check->fetch()) {
    // Já existe, redirecionar
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

// Redireciona com feedback
header("Location: service_detail.php?id=$service_id&hired=1");
exit();

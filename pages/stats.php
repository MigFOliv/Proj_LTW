<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_login();
include '../includes/header.php';

$user_id = $_SESSION['user_id'];

// Total de serviços
$stmt = $db->prepare("SELECT COUNT(*) FROM services WHERE freelancer_id = ?");
$stmt->execute([$user_id]);
$total_services = $stmt->fetchColumn();

// Total de pedidos recebidos
$stmt = $db->prepare("
    SELECT COUNT(*) 
    FROM transactions t
    JOIN services s ON t.service_id = s.id
    WHERE s.freelancer_id = ?
");
$stmt->execute([$user_id]);
$total_orders = $stmt->fetchColumn();

// Total concluídos
$stmt = $db->prepare("
    SELECT COUNT(*) 
    FROM transactions t
    JOIN services s ON t.service_id = s.id
    WHERE s.freelancer_id = ? AND t.status = 'completed'
");
$stmt->execute([$user_id]);
$completed = $stmt->fetchColumn();

// Média de avaliações
$stmt = $db->prepare("
    SELECT AVG(r.rating)
    FROM reviews r
    JOIN transactions t ON r.transaction_id = t.id
    JOIN services s ON t.service_id = s.id
    WHERE s.freelancer_id = ?
");
$stmt->execute([$user_id]);
$average = $stmt->fetchColumn();
?>

<main>
    <h2>📈 As Minhas Estatísticas</h2>
    <ul>
        <li><strong>Total de serviços publicados:</strong> <?= $total_services ?></li>
        <li><strong>Pedidos recebidos:</strong> <?= $total_orders ?></li>
        <li><strong>Pedidos concluídos:</strong> <?= $completed ?></li>
        <li><strong>Média de avaliações:</strong> <?= $average ? number_format($average, 1) . " / 5 ⭐" : 'Sem avaliações ainda' ?></li>
    </ul>

    <p><a href="dashboard.php">⬅️ Voltar ao Painel</a></p>
</main>

<?php include '../includes/footer.php'; ?>

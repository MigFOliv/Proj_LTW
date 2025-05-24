<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_login();
?>

<!DOCTYPE html>
<html lang="pt">
<?php include '../includes/head.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<body>
<?php include '../includes/header.php'; ?>

<?php
$user_id = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT COUNT(*) FROM services WHERE freelancer_id = ? AND status = 'aprovado'");
$stmt->execute([$user_id]);
$total_services = $stmt->fetchColumn();

$stmt = $db->prepare("
    SELECT COUNT(*) 
    FROM transactions t
    JOIN services s ON t.service_id = s.id
    WHERE s.freelancer_id = ?
");
$stmt->execute([$user_id]);
$total_orders = $stmt->fetchColumn();

$stmt = $db->prepare("
    SELECT COUNT(*) 
    FROM transactions t
    JOIN services s ON t.service_id = s.id
    WHERE s.freelancer_id = ? AND t.status = 'completed'
");
$stmt->execute([$user_id]);
$completed = $stmt->fetchColumn();

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

<main class="dashboard-container">
    <h2>📈 As Minhas Estatísticas</h2>

    <ul class="stats-list">
        <li><strong>Total de serviços publicados:</strong> <?= $total_services ?></li>
        <li><strong>Pedidos recebidos:</strong> <?= $total_orders ?></li>
        <li><strong>Pedidos Entregues:</strong> <?= $completed ?></li>
        <li><strong>Média de avaliações:</strong> <?= $average ? number_format($average, 1) . " / 5 ⭐" : 'Sem avaliações ainda' ?></li>
    </ul>

    <div style="max-width: 600px; margin: 2rem auto;">
        <canvas id="statsChart" width="400" height="200"></canvas>
    </div>

    <div class="dashboard-actions">
        <a href="dashboard.php" class="primary-btn">⬅️ Voltar ao Painel</a>
    </div>
</main>

<?php include '../includes/footer.php'; ?>

<script>
const ctx = document.getElementById('statsChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Serviços', 'Pedidos Recebidos', 'Pedidos Concluídos'],
        datasets: [{
            label: 'Totais',
            data: [<?= $total_services ?>, <?= $total_orders ?>, <?= $completed ?>],
            backgroundColor: ['#0070f3', '#00c853', '#ff6d00'],
            borderRadius: 10
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#000',
                titleColor: '#fff',
                bodyColor: '#fff'
            }
        }
    }
});
</script>
</body>
</html>

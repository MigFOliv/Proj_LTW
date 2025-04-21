<?php
require_once 'includes/auth.php';
require_login(); // Garante que o utilizador está autenticado

require_once 'includes/db.php';
include 'includes/header.php';

// Buscar os serviços do utilizador logado
$stmt = $db->prepare("SELECT * FROM services WHERE freelancer_id = :id ORDER BY id DESC");
$stmt->execute([':id' => $_SESSION['user_id']]);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>🎯 Painel do Freelancer</h2>
<p>Olá, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>! Aqui estão os teus serviços.</p>

<p><a href="add_service.php">➕ Adicionar novo serviço</a></p>

<?php if (count($services) === 0): ?>
    <p>Ainda não criaste nenhum serviço.</p>
<?php else: ?>
    <ul>
        <?php foreach ($services as $service): ?>
            <li>
                <strong><?= htmlspecialchars($service['title']) ?></strong> –
                <?= htmlspecialchars($service['price']) ?>€ –
                <?= htmlspecialchars($service['delivery_time']) ?>
                <br>
                <em><?= htmlspecialchars($service['description']) ?></em>
                <hr>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>

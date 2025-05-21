<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../includes/auth.php';
require_login(); 

require_once '../includes/db.php';
include '../includes/header.php';

$stmt = $db->prepare("SELECT * FROM services WHERE freelancer_id = :id ORDER BY id DESC");
$stmt->execute([':id' => $_SESSION['user_id']]);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main>
    <h2>🎯 Painel do Freelancer</h2>
    <p>Olá, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>! Aqui estão os teus serviços.</p>

    <p>
        <a href="add_service.php">
            <button class="primary-btn">➕ Adicionar novo serviço</button>
        </a>
    </p>

    <?php if (count($services) === 0): ?>
        <p>Ainda não criaste nenhum serviço.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($services as $service): ?>
                <li class="service-item">
                    <?php if (!empty($service['media_path']) && file_exists($service['media_path'])): ?>
                        <img src="<?= htmlspecialchars($service['media_path']) ?>" alt="Imagem do serviço" style="max-width: 100%; margin-bottom: 10px;">
                    <?php endif; ?>

                    <strong><?= htmlspecialchars($service['title']) ?></strong> –
                    <?= htmlspecialchars($service['price']) ?>€ –
                    <?= htmlspecialchars($service['delivery_time']) ?>
                    <br>
                    <em><?= htmlspecialchars($service['description']) ?></em>
                    <br><br>
                    <a href="edit_service.php?id=<?= $service['id'] ?>">
                        <button class="primary-btn">✏️ Editar</button>
                    </a>
                    <!--
                    <a href="delete_service.php?id=<?= $service['id'] ?>">
                        <button class="danger-btn">❌ Apagar</button>
                    </a>
                    -->
                    <hr>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>

<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/csrf.php';
require_login();
?>

<?php include '../includes/head.php'; ?>
<?php include '../includes/header.php'; ?>

<?php
$freelancer_id = $_GET['to'] ?? null;
$service_id = $_GET['service'] ?? null;

if (!$freelancer_id || !$service_id || $freelancer_id == $_SESSION['user_id'] || !is_numeric($freelancer_id) || !is_numeric($service_id)) {
    echo "<main class='dashboard-container'><p>Parâmetros inválidos.</p></main>";
    include '../includes/footer.php';
    exit();
}

$stmt = $db->prepare("
    SELECT s.title, u.username 
    FROM services s 
    JOIN users u ON s.freelancer_id = u.id 
    WHERE s.id = :sid AND u.id = :fid
");
$stmt->execute([':sid' => $service_id, ':fid' => $freelancer_id]);
$info = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$info) {
    echo "<main class='dashboard-container'><p>Serviço ou utilizador não encontrado.</p></main>";
    include '../includes/footer.php';
    exit();
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Token CSRF inválido.";
    } else {
        $content = trim($_POST['message'] ?? '');

        if (empty($content)) {
            $errors[] = "A mensagem não pode estar vazia.";
        } elseif (strlen($content) > 1000) {
            $errors[] = "A mensagem é demasiado longa (máx. 1000 caracteres).";
        }

        if (empty($errors)) {
            $insert = $db->prepare("
                INSERT INTO messages (sender_id, receiver_id, content) 
                VALUES (:from, :to, :msg)
            ");
            $insert->execute([
                ':from' => $_SESSION['user_id'],
                ':to' => $freelancer_id,
                ':msg' => $content
            ]);
            $success = true;
        }
    }
}
?>

<main class="dashboard-container">
    <h2>💬 Contactar <?= htmlspecialchars($info['username']) ?></h2>
    <p>Sobre o serviço: <strong><?= htmlspecialchars($info['title']) ?></strong></p>

    <?php foreach ($errors as $e): ?>
        <p class="error"><?= htmlspecialchars($e) ?></p>
    <?php endforeach; ?>

    <?php if ($success): ?>
        <p class="success">✅ Mensagem enviada com sucesso!</p>
    <?php else: ?>
        <form method="post" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

            <label>Mensagem:
                <textarea name="message" rows="4" required maxlength="1000" placeholder="Escreve a tua mensagem..."></textarea>
            </label>

            <button type="submit" class="primary-btn">📨 Enviar</button>
        </form>
    <?php endif; ?>

    <div class="dashboard-actions">
        <a href="service_detail.php?id=<?= htmlspecialchars($service_id) ?>" class="primary-btn">⬅️ Voltar ao serviço</a>
    </div>
</main>

<?php include '../includes/footer.php'; ?>

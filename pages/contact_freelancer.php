<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/csrf.php';
require_login();
include '../includes/header.php';

$freelancer_id = $_GET['to'] ?? null;
$service_id = $_GET['service'] ?? null;

if (!$freelancer_id || !$service_id || $freelancer_id == $_SESSION['user_id'] || !is_numeric($freelancer_id) || !is_numeric($service_id)) {
    echo "<p>Parâmetros inválidos.</p>";
    include '../includes/footer.php';
    exit();
}

// Obter info do freelancer e serviço
$stmt = $db->prepare("
    SELECT s.title, u.username 
    FROM services s 
    JOIN users u ON s.freelancer_id = u.id 
    WHERE s.id = :sid AND u.id = :fid
");
$stmt->execute([':sid' => $service_id, ':fid' => $freelancer_id]);
$info = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$info) {
    echo "<p>Serviço ou utilizador não encontrado.</p>";
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

<main>
    <h2>💬 Contactar <?= htmlspecialchars($info['username']) ?></h2>
    <p>Sobre o serviço: <strong><?= htmlspecialchars($info['title']) ?></strong></p>

    <?php foreach ($errors as $e): ?>
        <p style="color: red;"><?= htmlspecialchars($e) ?></p>
    <?php endforeach; ?>

    <?php if ($success): ?>
        <p style="color: green;">Mensagem enviada com sucesso!</p>
    <?php else: ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

            <label>Mensagem:
                <textarea name="message" rows="4" required maxlength="1000"></textarea>
            </label>
            <button type="submit" class="primary-btn">Enviar</button>
        </form>
    <?php endif; ?>

    <p><a href="service_detail.php?id=<?= htmlspecialchars($service_id) ?>">⬅️ Voltar ao serviço</a></p>
</main>

<?php include '../includes/footer.php'; ?>

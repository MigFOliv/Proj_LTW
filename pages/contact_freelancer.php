<?php
require_once '../includes/auth.php';
require_login();
require_once '../includes/db.php';
include '../includes/header.php';

$freelancer_id = $_GET['to'] ?? null;
$service_id = $_GET['service'] ?? null;

if (!$freelancer_id || !$service_id || $freelancer_id == $_SESSION['user_id']) {
    echo "<p>Parâmetros inválidos.</p>";
    include '../includes/footer.php';
    exit();
}

// Obter info do freelancer e serviço
$stmt = $db->prepare("SELECT s.title, u.username FROM services s JOIN users u ON s.freelancer_id = u.id WHERE s.id = :sid AND u.id = :fid");
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
    $content = trim($_POST['message'] ?? '');
    if (empty($content)) {
        $errors[] = "A mensagem não pode estar vazia.";
    } else {
        $insert = $db->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (:from, :to, :msg)");
        $insert->execute([
            ':from' => $_SESSION['user_id'],
            ':to' => $freelancer_id,
            ':msg' => $content
        ]);
        $success = true;
    }
}
?>

<h2>💬 Contactar <?= htmlspecialchars($info['username']) ?></h2>
<p>Sobre o serviço: <strong><?= htmlspecialchars($info['title']) ?></strong></p>

<?php foreach ($errors as $e): ?>
    <p style="color: red;"><?= htmlspecialchars($e) ?></p>
<?php endforeach; ?>

<?php if ($success): ?>
    <p style="color: green;">Mensagem enviada com sucesso!</p>
<?php endif; ?>

<form method="post">
    <label>Mensagem:
        <textarea name="message" rows="4" required></textarea>
    </label>
    <button type="submit" class="primary-btn">Enviar</button>
</form>

<p><a href="service_detail.php?id=<?= $service_id ?>">⬅️ Voltar ao serviço</a></p>

<?php include '../includes/footer.php'; ?>

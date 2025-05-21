<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/csrf.php';
require_login();
include '../includes/header.php';

$user_id = $_SESSION['user_id'];

// Validar ID da transação
if (!isset($_GET['transaction']) || !is_numeric($_GET['transaction'])) {
    echo "<main><p>Transação inválida.</p></main>";
    include '../includes/footer.php';
    exit();
}

$transaction_id = (int) $_GET['transaction'];

// Verificar se o cliente pode avaliar este serviço
$stmt = $db->prepare("
    SELECT t.*, s.title
    FROM transactions t
    JOIN services s ON t.service_id = s.id
    WHERE t.id = :id AND t.client_id = :uid AND t.status = 'completed'
");
$stmt->execute([':id' => $transaction_id, ':uid' => $user_id]);
$transaction = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaction) {
    echo "<main><p>Esta transação não existe, não pertence a ti, ou ainda não foi concluída.</p></main>";
    include '../includes/footer.php';
    exit();
}

// Verificar se já foi avaliado
$check = $db->prepare("SELECT 1 FROM reviews WHERE transaction_id = :id");
$check->execute([':id' => $transaction_id]);
if ($check->fetch()) {
    echo "<main><p>Este serviço já foi avaliado.</p></main>";
    include '../includes/footer.php';
    exit();
}

// Processar submissão
$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Token CSRF inválido.";
    } else {
        $rating = (int) ($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');

        if ($rating < 1 || $rating > 5) {
            $errors[] = "A avaliação deve ser entre 1 e 5 estrelas.";
        }

        if (strlen($comment) > 1000) {
            $errors[] = "O comentário é demasiado longo (máx. 1000 caracteres).";
        }

        if (empty($errors)) {
            $stmt = $db->prepare("
                INSERT INTO reviews (transaction_id, rating, comment)
                VALUES (:tid, :rating, :comment)
            ");
            $stmt->execute([
                ':tid' => $transaction_id,
                ':rating' => $rating,
                ':comment' => $comment
            ]);
            $success = true;
        }
    }
}
?>

<main>
    <h2>⭐ Avaliar Serviço</h2>
    <p><strong><?= htmlspecialchars($transaction['title']) ?></strong></p>

    <?php foreach ($errors as $e): ?>
        <p style="color: red;"><?= htmlspecialchars($e) ?></p>
    <?php endforeach; ?>

    <?php if ($success): ?>
        <p style="color: green;">✅ Avaliação enviada com sucesso!</p>
    <?php else: ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

            <label for="rating">Classificação (1 a 5):</label>
            <input type="number" name="rating" min="1" max="5" required>

            <label for="comment">Comentário (opcional):</label>
            <textarea name="comment" rows="4" maxlength="1000" placeholder="Deixa aqui o teu feedback..."></textarea>

            <button type="submit" class="primary-btn">Enviar Avaliação</button>
        </form>
    <?php endif; ?>

    <p><a href="my_orders.php">⬅️ Voltar aos meus pedidos</a></p>
</main>

<?php include '../includes/footer.php'; ?>

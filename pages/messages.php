<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/csrf.php';
require_login();

$user_id = $_SESSION['user_id'];
$errors = [];

// Enviar nova mensagem
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'], $_POST['receiver_id'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Token CSRF inválido.";
    } else {
        $content = trim($_POST['message']);
        $receiver = (int) $_POST['receiver_id'];

        if (empty($content)) {
            $errors[] = "A mensagem não pode estar vazia.";
        } elseif (strlen($content) > 1000) {
            $errors[] = "A mensagem é demasiado longa (máximo 1000 caracteres).";
        }

        if (empty($errors)) {
            $stmt = $db->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (:s, :r, :c)");
            $stmt->execute([
                ':s' => $user_id,
                ':r' => $receiver,
                ':c' => $content
            ]);
            header("Location: messages.php?user=$receiver");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<?php include '../includes/head.php'; ?>
<link rel="stylesheet" href="/css/chat.css">
<body>
<?php include '../includes/header.php'; ?>

<main class="dashboard-container">
  <h2>💬 Minhas Conversas</h2>

  <?php
  $stmt = $db->prepare("
      SELECT u.id, u.username
      FROM users u
      WHERE u.id != :me
      AND (
          u.id IN (SELECT sender_id FROM messages WHERE receiver_id = :me)
          OR
          u.id IN (SELECT receiver_id FROM messages WHERE sender_id = :me)
      )
      GROUP BY u.id
      ORDER BY u.username
  ");
  $stmt->execute([':me' => $user_id]);
  $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
  ?>

  <?php if (count($contacts) === 0): ?>
    <p>Ainda não tens mensagens com outros utilizadores.</p>
  <?php else: ?>
    <ul class="contacts-list">
      <?php foreach ($contacts as $contact): ?>
        <li class="service-item">
          <strong><?= htmlspecialchars($contact['username']) ?></strong><br>
          <a href="messages.php?user=<?= $contact['id'] ?>">
            <button class="primary-btn">📨 Ver Conversa</button>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <?php
  if (isset($_GET['user']) && is_numeric($_GET['user'])):
      $other_id = (int) $_GET['user'];

      $stmt = $db->prepare("SELECT username FROM users WHERE id = :id");
      $stmt->execute([':id' => $other_id]);
      $other = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($other):
          $stmt = $db->prepare("
              SELECT * FROM messages
              WHERE (sender_id = :me AND receiver_id = :other)
                 OR (sender_id = :other AND receiver_id = :me)
              ORDER BY timestamp
          ");
          $stmt->execute([':me' => $user_id, ':other' => $other_id]);
          $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
          ?>

          <div class="chat-container">
            <div class="chat-header">
              <h3>📨 Conversa com <?= htmlspecialchars($other['username']) ?></h3>
              <p class="chat-status">💡 <?= htmlspecialchars($other['username']) ?> está online • visto por último há 1 min</p>
            </div>

            <div id="chat-messages">
              <?php foreach ($messages as $msg): ?>
                <div class="message-bubble <?= $msg['sender_id'] == $user_id ? 'message-right' : 'message-left' ?>">
                  <?= nl2br(htmlspecialchars($msg['content'])) ?>
                </div>
              <?php endforeach; ?>
            </div>

            <?php foreach ($errors as $e): ?>
              <p class="error"><?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>

            <form method="post" class="chat-form">
              <input type="hidden" name="receiver_id" value="<?= $other_id ?>">
              <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
              <textarea name="message" rows="2" maxlength="1000" required placeholder="Escreve uma mensagem..."></textarea>
              <button type="submit" class="primary-btn">Enviar</button>
            </form>
          </div>

      <?php else: ?>
        <p>Utilizador não encontrado.</p>
      <?php endif; ?>
  <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const chatMessages = document.getElementById('chat-messages');
  if (chatMessages) {
    chatMessages.scrollTop = chatMessages.scrollHeight;
  }

  const form = document.querySelector('.chat-form');
  if (form) {
    form.addEventListener('submit', () => {
      const btn = form.querySelector('button');
      btn.disabled = true;
      btn.textContent = '⏳ A enviar...';
    });
  }
});
</script>
</body>
</html>

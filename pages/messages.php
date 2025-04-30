<?php
require_once '../includes/auth.php';
require_login();
require_once '../includes/db.php';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];

// Enviar nova mensagem
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'], $_POST['receiver_id'])) {
    $content = trim($_POST['message']);
    $receiver = (int) $_POST['receiver_id'];

    if (!empty($content)) {
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
?>

<main>
    <h2>💬 Minhas Conversas</h2>

    <?php
    // Buscar contactos (utilizadores com quem já falou)
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
        <ul>
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
    // Se um contacto específico for selecionado
    if (isset($_GET['user']) && is_numeric($_GET['user'])):
        $other_id = (int) $_GET['user'];

        $stmt = $db->prepare("SELECT username FROM users WHERE id = :id");
        $stmt->execute([':id' => $other_id]);
        $other = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($other):
            ?>
            <hr>
            <h3>📨 Conversa com <?= htmlspecialchars($other['username']) ?></h3>

            <?php
            $stmt = $db->prepare("
                SELECT * FROM messages
                WHERE (sender_id = :me AND receiver_id = :other)
                   OR (sender_id = :other AND receiver_id = :me)
                ORDER BY timestamp
            ");
            $stmt->execute([':me' => $user_id, ':other' => $other_id]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <div style="margin-bottom:1rem;">
                <?php foreach ($messages as $msg): ?>
                    <?php
                    $isMine = $msg['sender_id'] == $user_id;
                    $align = $isMine ? 'right' : 'left';
                    $bg = $isMine ? '#e1f5fe' : '#f0f0f0';
                    ?>
                    <div style="text-align: <?= $align ?>; margin: 0.5rem 0;">
                        <div style="display:inline-block; background: <?= $bg ?>; padding: 10px; border-radius: 8px; max-width: 70%;">
                            <?= htmlspecialchars($msg['content']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <form method="post">
                <input type="hidden" name="receiver_id" value="<?= $other_id ?>">
                <textarea name="message" rows="2" required placeholder="Escreve uma mensagem..."></textarea>
                <button type="submit" class="primary-btn">Enviar</button>
            </form>
        <?php
        else:
            echo "<p>Utilizador não encontrado.</p>";
        endif;
    endif;
    ?>
</main>

<?php include '../includes/footer.php'; ?>

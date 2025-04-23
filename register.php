<?php
require_once 'includes/db.php';
session_start();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password) || empty($confirm)) {
        $errors[] = "Todos os campos são obrigatórios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email inválido.";
    } elseif ($password !== $confirm) {
        $errors[] = "As passwords não coincidem.";
    }

    if (empty($errors)) {
        $stmt = $db->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
        $stmt->execute([':username' => $username, ':email' => $email]);

        if ($stmt->fetch()) {
            $errors[] = "Utilizador ou email já existe.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $db->prepare("INSERT INTO users (username, email, password_hash) VALUES (:u, :e, :p)");
            $insert->execute([
                ':u' => $username,
                ':e' => $email,
                ':p' => $hash
            ]);
            $success = true;
        }
    }
}

require_once 'includes/header.php';
?>

<main class="auth-container">
    <h2>📝 Registar Conta</h2>

    <?php foreach ($errors as $e): ?>
        <p style="color: red;"><?= htmlspecialchars($e) ?></p>
    <?php endforeach; ?>

    <?php if ($success): ?>
        <p style="color: green;">Conta criada com sucesso! <a href="login.php">Iniciar sessão</a></p>
    <?php else: ?>
        <form method="post">
            <label>Utilizador:
                <input type="text" name="username" required>
            </label>

            <label>Email:
                <input type="email" name="email" required>
            </label>

            <label>Password:
                <input type="password" name="password" required>
            </label>

            <label>Confirmar Password:
                <input type="password" name="confirm_password" required>
            </label>

            <button type="submit" class="primary-btn">Criar Conta</button>
        </form>
        <p style="text-align: center;">Já tens conta? <a href="login.php">Inicia sessão</a></p>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>

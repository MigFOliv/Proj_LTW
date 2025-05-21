<?php
require_once 'includes/db.php';
require_once 'includes/csrf.php';
session_start();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Token CSRF inválido.";
    } else {
        // Sanitizar inputs
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        // Validações
        if (empty($username) || empty($email) || empty($password) || empty($confirm)) {
            $errors[] = "Todos os campos são obrigatórios.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email inválido.";
        } elseif (strlen($username) < 3 || strlen($username) > 50) {
            $errors[] = "O nome de utilizador deve ter entre 3 e 50 caracteres.";
        } elseif (strlen($password) < 6) {
            $errors[] = "A password deve ter pelo menos 6 caracteres.";
        } elseif ($password !== $confirm) {
            $errors[] = "As passwords não coincidem.";
        }

        // Verificar duplicados e inserir utilizador
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
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

            <label>Utilizador:
                <input type="text" name="username" required minlength="3" maxlength="50">
            </label>

            <label>Email:
                <input type="email" name="email" required>
            </label>

            <label>Password:
                <input type="password" name="password" required minlength="6">
            </label>

            <label>Confirmar Password:
                <input type="password" name="confirm_password" required minlength="6">
            </label>

            <button type="submit" class="primary-btn">Criar Conta</button>
        </form>
        <p style="text-align: center;">Já tens conta? <a href="login.php">Inicia sessão</a></p>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>

<?php
require_once 'includes/db.php';
require_once 'includes/csrf.php';
session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Token CSRF inválido.";
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $errors[] = "Preenche todos os campos.";
        } else {
            $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = $user['is_admin'];
                header("Location: pages/dashboard.php");
                exit();
            } else {
                $errors[] = "Credenciais inválidas.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<?php include 'includes/head.php'; ?>
<body>

<?php include 'includes/header.php'; ?>

<main class="auth-container">
    <h2>🔐 Iniciar Sessão</h2>

    <?php foreach ($errors as $e): ?>
        <p class="error"><?= htmlspecialchars($e) ?></p>
    <?php endforeach; ?>

    <form method="post" class="auth-form">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

        <label>
            Utilizador:
            <input type="text" name="username" required>
        </label>

        <label>
            Password:
            <div style="position: relative;">
                <input type="password" name="password" id="password" required>
                <button type="button" onclick="togglePassword()" 
                        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
                               background: none; border: none; cursor: pointer;">
                    👁
                </button>
            </div>
        </label>

        <button type="submit" class="primary-btn">Entrar</button>
    </form>

    <p class="auth-footer">Ainda não tens conta? <a href="register.php">Regista-te aqui</a></p>
</main>

<?php include 'includes/footer.php'; ?>

<script>
function togglePassword() {
    const passwordField = document.getElementById('password');
    const type = passwordField.type === 'password' ? 'text' : 'password';
    passwordField.type = type;
}
</script>

</body>
</html>

<?php
require_once 'includes/db.php';
session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: pages/dashboard.php");
        exit();
    } else {
        $errors[] = "Credenciais inválidas.";
    }
}

require_once 'includes/header.php';
?>

<main class="auth-container">
    <h2>🔐 Iniciar Sessão</h2>

    <?php foreach ($errors as $e): ?>
        <p style="color: red;"><?= htmlspecialchars($e) ?></p>
    <?php endforeach; ?>

    <form method="post">
        <label>Utilizador:
            <input type="text" name="username" required>
        </label>

        <label>Password:
            <input type="password" name="password" required>
        </label>

        <button type="submit" class="primary-btn">Entrar</button>
    </form>

    <p style="text-align: center;">Ainda não tens conta? <a href="register.php">Regista-te aqui</a></p>
</main>

<?php include 'includes/footer.php'; ?>

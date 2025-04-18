<?php
session_start();
require_once 'includes/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Verifica se o utilizador existe
    $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'];
        header('Location: index.php');
        exit();
    } else {
        $errors[] = "Credenciais inválidas.";
    }
}
?>

<h2>Iniciar Sessão</h2>

<?php foreach ($errors as $error): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endforeach; ?>

<form method="post">
    <label>Utilizador:<br><input type="text" name="username" required></label><br><br>
    <label>Password:<br><input type="password" name="password" required></label><br><br>
    <button type="submit">Entrar</button>
</form>

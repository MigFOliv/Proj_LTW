<?php
require_once 'includes/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validações básicas
    if (empty($username) || empty($email) || empty($password)) {
        $errors[] = "Preenche todos os campos!";
    }

    // Verificar se o utilizador ou email já existem
    if (empty($errors)) {
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = :username OR email = :email");
        $stmt->execute([':username' => $username, ':email' => $email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Nome de utilizador ou email já estão registados.";
        }
    }

    // Inserir no banco
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :hash)");
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':hash' => $hash
        ]);
        echo "<p>Conta criada com sucesso!</p>";
    }
}
?>

<h2>Registar nova conta</h2>

<?php
foreach ($errors as $error) {
    echo "<p style='color:red;'>$error</p>";
}
?>

<form method="post">
    <label>Nome de utilizador:<br>
        <input type="text" name="username" required>
    </label><br><br>

    <label>Email:<br>
        <input type="email" name="email" required>
    </label><br><br>

    <label>Password:<br>
        <input type="password" name="password" required>
    </label><br><br>

    <button type="submit">Registar</button>
</form>

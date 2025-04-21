<?php
require_once 'includes/auth.php';
require_login();
require_once 'includes/db.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $delivery = trim($_POST['delivery_time'] ?? '');
    $category = trim($_POST['category'] ?? '');

    // Validação simples
    if (empty($title) || empty($description) || $price <= 0 || empty($delivery)) {
        $errors[] = "Todos os campos obrigatórios devem ser preenchidos corretamente.";
    }

    if (empty($errors)) {
        $stmt = $db->prepare("INSERT INTO services (freelancer_id, title, description, price, delivery_time, category)
                              VALUES (:fid, :title, :desc, :price, :delivery, :cat)");
        $stmt->execute([
            ':fid' => $_SESSION['user_id'],
            ':title' => $title,
            ':desc' => $description,
            ':price' => $price,
            ':delivery' => $delivery,
            ':cat' => $category
        ]);
        $success = true;
    }
}
?>

<?php include 'includes/header.php'; ?>

<h2>➕ Criar Novo Serviço</h2>

<?php foreach ($errors as $e): ?>
    <p style="color: red;"><?= htmlspecialchars($e) ?></p>
<?php endforeach; ?>

<?php if ($success): ?>
    <p style="color: green;">✅ Serviço criado com sucesso!</p>
<?php endif; ?>

<form method="post">
    <label>Título:<br>
        <input type="text" name="title" required>
    </label><br><br>

    <label>Descrição:<br>
        <textarea name="description" rows="4" required></textarea>
    </label><br><br>

    <label>Preço (€):<br>
        <input type="number" name="price" step="0.01" required>
    </label><br><br>

    <label>Tempo de entrega:<br>
        <input type="text" name="delivery_time" placeholder="ex: 3 dias" required>
    </label><br><br>

    <label>Categoria (texto livre):<br>
        <input type="text" name="category">
    </label><br><br>

    <button type="submit">Criar Serviço</button>
</form>

<p><a href="pages/dashboard.php">⬅️ Voltar ao painel</a></p>

<?php include 'includes/footer.php'; ?>

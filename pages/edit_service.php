<?php
require_once '../includes/auth.php';
require_login();
require_once '../includes/db.php';

$service_id = $_GET['id'] ?? null;

// Verificar se o ID é válido
if (!$service_id) {
    die("ID de serviço inválido.");
}

// Obter os dados atuais do serviço
$stmt = $db->prepare("SELECT * FROM services WHERE id = :id AND freelancer_id = :uid");
$stmt->execute([
    ':id' => $service_id,
    ':uid' => $_SESSION['user_id']
]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    die("Serviço não encontrado ou não tens permissão para o editar.");
}

$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $delivery = trim($_POST['delivery_time'] ?? '');
    $category = trim($_POST['category'] ?? '');

    if (empty($title) || empty($description) || $price <= 0 || empty($delivery)) {
        $errors[] = "Preenche todos os campos obrigatórios corretamente.";
    }

    if (empty($errors)) {
        $update = $db->prepare("UPDATE services SET title = :title, description = :desc, price = :price, delivery_time = :delivery, category = :category WHERE id = :id AND freelancer_id = :uid");
        $update->execute([
            ':title' => $title,
            ':desc' => $description,
            ':price' => $price,
            ':delivery' => $delivery,
            ':category' => $category,
            ':id' => $service_id,
            ':uid' => $_SESSION['user_id']
        ]);
        $success = true;

        // Atualiza os dados do formulário para mostrar os novos valores
        $service = array_merge($service, [
            'title' => $title,
            'description' => $description,
            'price' => $price,
            'delivery_time' => $delivery,
            'category' => $category
        ]);
    }
}
?>

<?php include '../includes/header.php'; ?>

<h2>✏️ Editar Serviço</h2>

<?php foreach ($errors as $e): ?>
    <p style="color:red"><?= htmlspecialchars($e) ?></p>
<?php endforeach; ?>

<?php if ($success): ?>
    <p style="color:green">✅ Serviço atualizado com sucesso!</p>
<?php endif; ?>

<form method="post">
    <label>Título:<br>
        <input type="text" name="title" value="<?= htmlspecialchars($service['title']) ?>" required>
    </label><br><br>

    <label>Descrição:<br>
        <textarea name="description" rows="4" required><?= htmlspecialchars($service['description']) ?></textarea>
    </label><br><br>

    <label>Preço (€):<br>
        <input type="number" name="price" step="0.01" value="<?= htmlspecialchars($service['price']) ?>" required>
    </label><br><br>

    <label>Tempo de entrega:<br>
        <input type="text" name="delivery_time" value="<?= htmlspecialchars($service['delivery_time']) ?>" required>
    </label><br><br>

    <label>Categoria:<br>
        <input type="text" name="category" value="<?= htmlspecialchars($service['category']) ?>">
    </label><br><br>

    <button type="submit">💾 Guardar alterações</button>
</form>

<p><a href="dashboard.php">⬅️ Voltar ao Painel</a></p>

<?php include '../includes/footer.php'; ?>

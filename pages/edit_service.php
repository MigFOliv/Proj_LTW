<?php
require_once '../includes/auth.php';
require_login();
require_once '../includes/db.php';

$service_id = $_GET['id'] ?? null;

if (!$service_id) {
    die("ID de serviço inválido.");
}

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

    $newMediaPath = $service['media_path'];

    // Upload de nova imagem (se enviada)
    if (!empty($_FILES['media']['name'])) {
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir);
        $filename = time() . '_' . basename($_FILES['media']['name']);
        $target = $uploadDir . $filename;

        $fileType = strtolower(pathinfo($target, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileType, $allowed) && move_uploaded_file($_FILES['media']['tmp_name'], $target)) {
            $newMediaPath = $target;
        } else {
            $errors[] = "Erro ao carregar a imagem. Tipos permitidos: JPG, PNG, GIF.";
        }
    }

    if (empty($title) || empty($description) || $price <= 0 || empty($delivery)) {
        $errors[] = "Preenche todos os campos obrigatórios corretamente.";
    }

    if (empty($errors)) {
        $update = $db->prepare("UPDATE services SET title = :title, description = :desc, price = :price, delivery_time = :delivery, category = :category, media_path = :media WHERE id = :id AND freelancer_id = :uid");
        $update->execute([
            ':title' => $title,
            ':desc' => $description,
            ':price' => $price,
            ':delivery' => $delivery,
            ':category' => $category,
            ':media' => $newMediaPath,
            ':id' => $service_id,
            ':uid' => $_SESSION['user_id']
        ]);
        $success = true;

        $service = array_merge($service, [
            'title' => $title,
            'description' => $description,
            'price' => $price,
            'delivery_time' => $delivery,
            'category' => $category,
            'media_path' => $newMediaPath
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

<form method="post" enctype="multipart/form-data">
    <label>Título:<br>
        <input type="text" name="title" value="<?= htmlspecialchars($service['title']) ?>" required>
    </label><br>

    <label>Descrição:<br>
        <textarea name="description" rows="4" required><?= htmlspecialchars($service['description']) ?></textarea>
    </label><br>

    <label>Preço (€):<br>
        <input type="number" name="price" step="0.01" value="<?= htmlspecialchars($service['price']) ?>" required>
    </label><br>

    <label>Tempo de entrega:<br>
        <input type="text" name="delivery_time" value="<?= htmlspecialchars($service['delivery_time']) ?>" required>
    </label><br>

    <label>Categoria:<br>
        <input type="text" name="category" value="<?= htmlspecialchars($service['category']) ?>">
    </label><br>

    <?php if (!empty($service['media_path']) && file_exists($service['media_path'])): ?>
        <p><strong>Imagem atual:</strong></p>
        <img src="<?= htmlspecialchars($service['media_path']) ?>" alt="Imagem atual" style="max-width: 200px;"><br>
    <?php endif; ?>

    <label>Nova imagem (opcional):<br>
        <input type="file" name="media" accept="image/*">
    </label><br>

    <button type="submit">💾 Guardar alterações</button>
</form>

<p><a href="dashboard.php">⬅️ Voltar ao Painel</a></p>

<?php include '../includes/footer.php'; ?>


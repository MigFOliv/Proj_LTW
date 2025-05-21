<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/csrf.php';
require_login();

$service_id = $_GET['id'] ?? null;

if (!$service_id || !is_numeric($service_id)) {
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
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Token CSRF inválido.";
    } else {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $delivery = trim($_POST['delivery_time'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $is_promoted = isset($_POST['is_promoted']) ? 1 : 0;
        $newMediaPath = $service['media_path'];

        // Upload de nova imagem (opcional)
        if (!empty($_FILES['media']['name']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
            $tmp = $_FILES['media']['tmp_name'];
            $mime = mime_content_type($tmp);
            $allowed = ['image/jpeg', 'image/png', 'image/gif'];

            if (in_array($mime, $allowed)) {
                $ext = pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION);
                $filename = 'service_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
                $dest = '../uploads/' . $filename;

                if (move_uploaded_file($tmp, $dest)) {
                    $newMediaPath = 'uploads/' . $filename;
                } else {
                    $errors[] = "Erro ao guardar a imagem.";
                }
            } else {
                $errors[] = "Tipo de ficheiro inválido. Apenas JPG, PNG e GIF são permitidos.";
            }
        }

        // Validações
        if (strlen($title) < 3 || strlen($description) < 10 || $price <= 0 || strlen($delivery) < 3) {
            $errors[] = "Preenche todos os campos obrigatórios corretamente.";
        }

        if (empty($errors)) {
            $update = $db->prepare("
                UPDATE services 
                SET title = :title, description = :desc, price = :price, 
                    delivery_time = :delivery, category = :category, 
                    media_path = :media, is_promoted = :promoted
                WHERE id = :id AND freelancer_id = :uid
            ");
            $update->execute([
                ':title' => $title,
                ':desc' => $description,
                ':price' => $price,
                ':delivery' => $delivery,
                ':category' => $category,
                ':media' => $newMediaPath,
                ':promoted' => $is_promoted,
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
                'media_path' => $newMediaPath,
                'is_promoted' => $is_promoted
            ]);
        }
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
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

    <label>Título:<br>
        <input type="text" name="title" value="<?= htmlspecialchars($service['title']) ?>" required minlength="3">
    </label><br>

    <label>Descrição:<br>
        <textarea name="description" rows="4" required minlength="10"><?= htmlspecialchars($service['description']) ?></textarea>
    </label><br>

    <label>Preço (€):<br>
        <input type="number" name="price" step="0.01" min="1" value="<?= htmlspecialchars($service['price']) ?>" required>
    </label><br>

    <label>Tempo de entrega:<br>
        <input type="text" name="delivery_time" value="<?= htmlspecialchars($service['delivery_time']) ?>" required>
    </label><br>

    <label>Categoria:<br>
        <input type="text" name="category" value="<?= htmlspecialchars($service['category']) ?>">
    </label><br>

    <label>
        <input type="checkbox" name="is_promoted" value="1" <?= $service['is_promoted'] ? 'checked' : '' ?>>
        ⭐ Destacar serviço na página inicial
    </label><br>

    <?php if (!empty($service['media_path']) && file_exists('../' . $service['media_path'])): ?>
        <p><strong>Imagem atual:</strong></p>
        <img src="/<?= htmlspecialchars($service['media_path']) ?>" alt="Imagem atual" style="max-width: 200px;"><br>
    <?php endif; ?>

    <label>Nova imagem (opcional):<br>
        <input type="file" name="media" accept="image/*">
    </label><br>

    <button type="submit">💾 Guardar alterações</button>
</form>

<p><a href="dashboard.php">⬅️ Voltar ao Painel</a></p>

<?php include '../includes/footer.php'; ?>

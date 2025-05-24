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
        $currency = trim($_POST['currency'] ?? 'USD');
        $delivery = trim($_POST['delivery_time'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $is_promoted = isset($_POST['is_promoted']) ? 1 : 0;
        $newMediaPath = $service['media_path'];

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
                $errors[] = "Tipo de ficheiro inválido.";
            }
        }

        if (strlen($title) < 3 || strlen($description) < 10 || $price <= 0 || strlen($delivery) < 3) {
            $errors[] = "Preenche todos os campos obrigatórios corretamente.";
        }

        if (empty($errors)) {
            $update = $db->prepare("
                UPDATE services 
                SET title = :title, description = :desc, price = :price, 
                    currency = :currency, delivery_time = :delivery, category = :category, 
                    media_path = :media, is_promoted = :promoted, status = 'pendente'
                WHERE id = :id AND freelancer_id = :uid
            ");
            $update->execute([
                ':title' => $title,
                ':desc' => $description,
                ':price' => $price,
                ':currency' => $currency,
                ':delivery' => $delivery,
                ':category' => $category,
                ':media' => $newMediaPath,
                ':promoted' => $is_promoted,
                ':id' => $service_id,
                ':uid' => $_SESSION['user_id']
            ]);
            $success = true;
        }
    }
}
?>

<?php include '../includes/head.php'; ?>
<?php include '../includes/header.php'; ?>

<main class="dashboard-container">
    <h2>✏️ Editar Serviço</h2>

    <?php foreach ($errors as $e): ?>
        <p class="error"><?= htmlspecialchars($e) ?></p>
    <?php endforeach; ?>

    <?php if ($success): ?>
        <p class="success">✅ Serviço atualizado com sucesso! Está agora em análise para nova aprovação.</p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="auth-form" style="max-width: 600px; margin: 0 auto;">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

        <label>Título:
            <input type="text" name="title" value="<?= htmlspecialchars($service['title']) ?>" required minlength="3">
        </label>

        <label>Descrição:
            <textarea name="description" rows="4" required minlength="10"><?= htmlspecialchars($service['description']) ?></textarea>
        </label>

        <label>Preço:
            <input type="number" name="price" step="0.01" min="1" value="<?= htmlspecialchars($service['price']) ?>" required>
        </label>

        <label>Moeda:
            <select name="currency">
                <option value="USD" <?= $service['currency'] === 'USD' ? 'selected' : '' ?>>USD</option>
                <option value="EUR" <?= $service['currency'] === 'EUR' ? 'selected' : '' ?>>EUR</option>
                <option value="GBP" <?= $service['currency'] === 'GBP' ? 'selected' : '' ?>>GBP</option>
                <option value="BRL" <?= $service['currency'] === 'BRL' ? 'selected' : '' ?>>BRL</option>
            </select>
        </label>

        <label>Tempo de entrega:
            <input type="text" name="delivery_time" value="<?= htmlspecialchars($service['delivery_time']) ?>" required>
        </label>

        <label>Categoria:
            <input type="text" name="category" value="<?= htmlspecialchars($service['category']) ?>">
        </label>

        <label>
            <input type="checkbox" name="is_promoted" value="1" <?= $service['is_promoted'] ? 'checked' : '' ?>>
            ⭐ Destacar serviço na página inicial
        </label>

        <?php if (!empty($service['media_path']) && file_exists('../' . $service['media_path'])): ?>
            <p><strong>Imagem atual:</strong></p>
            <img src="/<?= htmlspecialchars($service['media_path']) ?>" alt="Imagem atual" class="service-image">
        <?php endif; ?>

        <label>Nova imagem (opcional):
            <input type="file" name="media" accept="image/*">
        </label>

        <button type="submit" class="primary-btn">💾 Guardar alterações</button>
    </form>

    <div class="dashboard-actions">
        <a href="dashboard.php" class="primary-btn">⬅️ Voltar ao Painel</a>
    </div>
</main>

<?php include '../includes/footer.php'; ?>

<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/csrf.php';
require_login();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Token CSRF inválido.";
    } else {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $delivery = trim($_POST['delivery_time'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $mediaPath = null;

        // Validação de dados
        if (strlen($title) < 3 || strlen($description) < 10 || $price <= 0 || strlen($delivery) < 3) {
            $errors[] = "Todos os campos obrigatórios devem ser preenchidos corretamente.";
        }

        // Validação e upload de imagem
        if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
            $tmp = $_FILES['media']['tmp_name'];
            $type = mime_content_type($tmp);
            $allowed = ['image/jpeg', 'image/png', 'image/gif'];

            if (in_array($type, $allowed)) {
                $ext = pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION);
                $safeName = 'service_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
                $destination = '../uploads/' . $safeName;

                if (move_uploaded_file($tmp, $destination)) {
                    $mediaPath = 'uploads/' . $safeName;
                } else {
                    $errors[] = "Erro ao guardar a imagem.";
                }
            } else {
                $errors[] = "Tipo de ficheiro inválido. Apenas JPG, PNG e GIF são permitidos.";
            }
        }

        // Inserir serviço
        if (empty($errors)) {
            $stmt = $db->prepare("INSERT INTO services (freelancer_id, title, description, price, delivery_time, category, media_path)
                                  VALUES (:fid, :title, :desc, :price, :delivery, :cat, :media)");
            $stmt->execute([
                ':fid' => $_SESSION['user_id'],
                ':title' => $title,
                ':desc' => $description,
                ':price' => $price,
                ':delivery' => $delivery,
                ':cat' => $category,
                ':media' => $mediaPath
            ]);
            $success = true;
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<h2>➕ Criar Novo Serviço</h2>

<?php foreach ($errors as $e): ?>
    <p style="color: red;"><?= htmlspecialchars($e) ?></p>
<?php endforeach; ?>

<?php if ($success): ?>
    <p style="color: green;">✅ Serviço criado com sucesso!</p>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

    <label>Título:<br>
        <input type="text" name="title" required minlength="3" maxlength="100">
    </label><br><br>

    <label>Descrição:<br>
        <textarea name="description" rows="4" required minlength="10"></textarea>
    </label><br><br>

    <label>Preço (€):<br>
        <input type="number" name="price" step="0.01" required min="1">
    </label><br><br>

    <label>Tempo de entrega:<br>
        <input type="text" name="delivery_time" placeholder="ex: 3 dias" required>
    </label><br><br>

    <label>Categoria (texto livre):<br>
        <input type="text" name="category" maxlength="50">
    </label><br><br>

    <label>Imagem do serviço:<br>
        <input type="file" name="media" accept="image/*">
    </label><br><br>

    <button type="submit">Criar Serviço</button>
</form>

<p><a href="dashboard.php">⬅️ Voltar ao painel</a></p>

<?php include '../includes/footer.php'; ?>

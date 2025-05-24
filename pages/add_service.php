<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/csrf.php';
require_login();

$errors = [];
$success = false;

$availableCurrencies = ['EUR', 'USD', 'BRL', 'GBP', 'JPY'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Token CSRF inválido.";
    } else {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $delivery = trim($_POST['delivery_time'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $currency = strtoupper(trim($_POST['currency'] ?? 'EUR'));
        $mediaPath = null;

        if (!in_array($currency, $availableCurrencies)) {
            $errors[] = "Moeda inválida.";
        }

        if (strlen($title) < 3 || strlen($description) < 10 || $price <= 0 || strlen($delivery) < 3) {
            $errors[] = "Todos os campos obrigatórios devem ser preenchidos corretamente.";
        }

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

        if (empty($errors)) {
            // Inserir categoria como pendente se não existir ainda
            $check = $db->prepare("SELECT 1 FROM categories WHERE LOWER(name) = LOWER(:name)");
            $check->execute([':name' => $category]);
            if (!$check->fetch()) {
                $insert = $db->prepare("INSERT INTO categories (name, approved) VALUES (:name, 0)");
                $insert->execute([':name' => $category]);
            }

            // Inserir o serviço
            $stmt = $db->prepare("INSERT INTO services (
                freelancer_id, title, description, price, currency, delivery_time, category, media_path, status
            ) VALUES (
                :fid, :title, :desc, :price, :currency, :delivery, :cat, :media, 'pendente'
            )");
            $stmt->execute([
                ':fid' => $_SESSION['user_id'],
                ':title' => $title,
                ':desc' => $description,
                ':price' => $price,
                ':currency' => $currency,
                ':delivery' => $delivery,
                ':cat' => $category,
                ':media' => $mediaPath
            ]);
            
            $success = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<?php include '../includes/head.php'; ?>
<body>
<?php include '../includes/header.php'; ?>

<main class="dashboard-container">
    <h2>➕ Criar Novo Serviço</h2>

    <?php foreach ($errors as $e): ?>
        <p class="error"><?= htmlspecialchars($e) ?></p>
    <?php endforeach; ?>

    <?php if ($success): ?>
        <p class="success">✅ Serviço criado com sucesso!</p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="auth-form" style="max-width: 600px; margin: 2rem auto;">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

        <label>Título:
            <input type="text" name="title" required minlength="3" maxlength="100">
        </label>

        <label>Descrição:
            <textarea name="description" rows="4" required minlength="10"></textarea>
        </label>

        <label>Preço:
            <input type="number" name="price" step="0.01" required min="1">
        </label>

        <label>Moeda:
            <select name="currency" required>
                <?php foreach ($availableCurrencies as $cur): ?>
                    <option value="<?= $cur ?>"><?= $cur ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>Tempo de entrega:
            <input type="text" name="delivery_time" placeholder="ex: 3 dias" required>
        </label>

        <label>Categoria (texto livre):
            <input type="text" name="category" maxlength="50">
        </label>

        <label>Imagem do serviço:
            <input type="file" name="media" accept="image/*">
        </label>

        <button type="submit" class="primary-btn">Criar Serviço</button>
    </form>

    <p style="text-align: center;"><a href="dashboard.php">⬅️ Voltar ao painel</a></p>
</main>

<?php include '../includes/footer.php'; ?>
</body>
</html>

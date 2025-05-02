<?php
require_once '../includes/header.php';
require_once '../includes/db.php';

// Obter categorias distintas para o filtro
$categoriesStmt = $db->query("SELECT DISTINCT category FROM services WHERE category IS NOT NULL AND category != ''");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_COLUMN);

// Capturar filtros do formulário
$selectedCategory = $_GET['category'] ?? '';
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';

// Construir a query com filtros
$query = "SELECT s.*, u.username FROM services s JOIN users u ON s.freelancer_id = u.id WHERE 1=1";
$params = [];

if (!empty($selectedCategory)) {
    $query .= " AND s.category = :category";
    $params[':category'] = $selectedCategory;
}
if (is_numeric($minPrice)) {
    $query .= " AND s.price >= :min";
    $params[':min'] = $minPrice;
}
if (is_numeric($maxPrice)) {
    $query .= " AND s.price <= :max";
    $params[':max'] = $maxPrice;
}

$query .= " ORDER BY s.id DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>🌐 Todos os Serviços Disponíveis</h2>

<!-- Formulário de Filtros -->
<form method="get" style="margin-bottom: 20px;">
    <label>
        Categoria:
        <select name="category">
            <option value="">Todas</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>" <?= $selectedCategory === $cat ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>Preço mínimo:
        <input type="number" step="0.01" name="min_price" value="<?= htmlspecialchars($minPrice) ?>">
    </label>

    <label>Preço máximo:
        <input type="number" step="0.01" name="max_price" value="<?= htmlspecialchars($maxPrice) ?>">
    </label>

    <button type="submit" class="primary-btn">🔎 Filtrar</button>
</form>

<?php if (count($services) === 0): ?>
    <p>Não há serviços disponíveis de momento.</p>
<?php else: ?>
    <?php foreach ($services as $s): ?>
        <?php
        $stmtRating = $db->prepare("
            SELECT AVG(r.rating) as avg_rating
            FROM reviews r
            JOIN transactions t ON r.transaction_id = t.id
            WHERE t.service_id = :sid
        ");
        $stmtRating->execute([':sid' => $s['id']]);
        $avg = $stmtRating->fetch(PDO::FETCH_ASSOC)['avg_rating'];
        ?>
        <div class="service-item">
            <h3><?= htmlspecialchars($s['title']) ?></h3>
            <?php if ($avg !== null): ?>
                <p>⭐ <?= number_format($avg, 1) ?> / 5</p>
            <?php endif; ?>
            <p><em><?= htmlspecialchars($s['description']) ?></em></p>
            <p><strong><?= htmlspecialchars($s['price']) ?>€</strong> • Entrega: <?= htmlspecialchars($s['delivery_time']) ?></p>
            <p><small>Por <strong><?= htmlspecialchars($s['username']) ?></strong> • Categoria: <?= htmlspecialchars($s['category'] ?? '—') ?></small></p>

            <a href="service_detail.php?id=<?= $s['id'] ?>">
                <button class="primary-btn">🔍 Ver mais</button>
            </a>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>

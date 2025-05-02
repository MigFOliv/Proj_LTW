<?php
require_once '../includes/header.php';
require_once '../includes/db.php';

// Obter categorias existentes
$categoriesStmt = $db->query("SELECT DISTINCT category FROM services WHERE category IS NOT NULL AND category != ''");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_COLUMN);

// Filtros recebidos por GET
$selectedCategory = $_GET['category'] ?? '';
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';
$sort = $_GET['sort'] ?? 'latest';

// Construir query dinâmica
$query = "
    SELECT s.*, u.username,
        (SELECT AVG(r.rating)
         FROM reviews r
         JOIN transactions t ON r.transaction_id = t.id
         WHERE t.service_id = s.id) as avg_rating
    FROM services s
    JOIN users u ON s.freelancer_id = u.id
    WHERE 1=1
";

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

// Ordenação
switch ($sort) {
    case 'price_asc':
        $query .= " ORDER BY s.price ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY s.price DESC";
        break;
    case 'rating':
        $query .= " ORDER BY avg_rating DESC NULLS LAST";
        break;
    default:
        $query .= " ORDER BY s.id DESC"; // latest
}

$stmt = $db->prepare($query);
$stmt->execute($params);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>🌐 Todos os Serviços Disponíveis</h2>

<!-- Filtros e ordenação -->
<form method="get" style="margin-bottom: 20px;">
    <label>Categoria:
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

    <label>Ordenar por:
        <select name="sort">
            <option value="latest" <?= $sort === 'latest' ? 'selected' : '' ?>>Mais Recentes</option>
            <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Preço (Menor → Maior)</option>
            <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Preço (Maior → Menor)</option>
            <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Avaliação</option>
        </select>
    </label>

    <button type="submit" class="primary-btn">🔎 Aplicar Filtros</button>
</form>

<?php if (count($services) === 0): ?>
    <p>Não há serviços disponíveis de momento.</p>
<?php else: ?>
    <?php foreach ($services as $s): ?>
        <div class="service-item">
            <h3><?= htmlspecialchars($s['title']) ?></h3>
            <?php if ($s['avg_rating'] !== null): ?>
                <p>⭐ <?= number_format($s['avg_rating'], 1) ?> / 5</p>
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

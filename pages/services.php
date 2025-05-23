<?php
require_once '../includes/db.php';
require_once '../includes/header.php';

// Obter categorias distintas
$categoriesStmt = $db->query("SELECT DISTINCT category FROM services WHERE category IS NOT NULL AND category != ''");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_COLUMN);

// Guardar filtros do utilizador
$selectedCategory = $_GET['category'] ?? '';
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';
$sort = $_GET['sort'] ?? 'latest';
?>

<!DOCTYPE html>
<html lang="pt">
<?php include '../includes/head.php'; ?>
<body>

<main class="services-page">
    <h2>🌐 Todos os Serviços Disponíveis</h2>

    <!-- Filtros -->
    <form id="filter-form" method="get" class="filter-form">
        <div class="filter-group">
            <label>Categoria:
                <select name="category">
                    <option value="">Todas</option>
                    <?php if (count($categories) === 0): ?>
                        <option disabled>Nenhuma categoria disponível</option>
                    <?php else: ?>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>" <?= $cat === $selectedCategory ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
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
                </select>
            </label>

            <button type="submit" class="primary-btn">🔎 Aplicar Filtros</button>
        </div>
    </form>

    <!-- Resultados AJAX -->
    <div id="service-results" class="card-grid"></div>
</main>

<?php include '../includes/footer.php'; ?>

<script>
document.getElementById('filter-form').addEventListener('submit', function (e) {
    e.preventDefault();
    const form = this;
    const btn = form.querySelector('button');
    btn.disabled = true;
    btn.textContent = '🔄 A carregar...';

    const params = new URLSearchParams(new FormData(form)).toString();

    fetch('fetch_services.php?' + params)
        .then(res => res.text())
        .then(html => {
            document.getElementById('service-results').innerHTML = html;
        })
        .finally(() => {
            btn.disabled = false;
            btn.textContent = '🔎 Aplicar Filtros';
        });
});

// Carregamento inicial
window.addEventListener('DOMContentLoaded', () => {
    document.getElementById('filter-form').dispatchEvent(new Event('submit'));
});
</script>
</body>
</html>

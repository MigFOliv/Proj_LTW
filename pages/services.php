<?php
require_once '../includes/header.php';
require_once '../includes/db.php';

// Obter categorias existentes
$categoriesStmt = $db->query("SELECT DISTINCT category FROM services WHERE category IS NOT NULL AND category != ''");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_COLUMN);
?>

<h2>🌐 Todos os Serviços Disponíveis</h2>

<!-- Filtros e ordenação -->
<form id="filter-form" method="get" style="margin-bottom: 20px;">
    <label>Categoria:
        <select name="category">
            <option value="">Todas</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>">
                    <?= htmlspecialchars($cat) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>Preço mínimo:
        <input type="number" step="0.01" name="min_price">
    </label>

    <label>Preço máximo:
        <input type="number" step="0.01" name="max_price">
    </label>

    <label>Ordenar por:
        <select name="sort">
            <option value="latest">Mais Recentes</option>
            <option value="price_asc">Preço (Menor → Maior)</option>
            <option value="price_desc">Preço (Maior → Menor)</option>
            <option value="rating">Avaliação</option>
        </select>
    </label>

    <button type="submit" class="primary-btn">🔎 Aplicar Filtros</button>
</form>

<!-- Resultados dinâmicos -->
<div id="service-results">
    <!-- Conteúdo carregado por AJAX -->
</div>

<script>
// Envia o formulário por AJAX e atualiza os resultados
document.getElementById('filter-form').addEventListener('submit', function (e) {
    e.preventDefault();
    const params = new URLSearchParams(new FormData(this)).toString();

    fetch('fetch_services.php?' + params)
        .then(res => res.text())
        .then(html => {
            document.getElementById('service-results').innerHTML = html;
        });
});

// Carrega os serviços ao abrir a página
window.addEventListener('DOMContentLoaded', () => {
    document.getElementById('filter-form').dispatchEvent(new Event('submit'));
});
</script>

<?php include '../includes/footer.php'; ?>
<?php if (isset($_SESSION['user_id'])): ?>
    <button class="primary-btn" onclick="toggleFavorite(<?= $s['id'] ?>, this)">
        ☆ Adicionar aos Favoritos
    </button>
<?php endif; ?>


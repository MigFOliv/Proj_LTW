<?php
require_once '../includes/db.php';

$selectedCategory = $_GET['category'] ?? '';
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';
$sort = $_GET['sort'] ?? 'latest';

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
        $query .= " ORDER BY s.id DESC";
}

$stmt = $db->prepare($query);
$stmt->execute($params);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($services) === 0) {
    echo "<p>Não há serviços disponíveis de momento.</p>";
} else {
    foreach ($services as $s) {
        echo '<div class="service-item">';
        if (!empty($s['media_path']) && file_exists($s['media_path'])) {
            echo '<img src="' . htmlspecialchars($s['media_path']) . '" alt="Imagem do serviço" style="max-width: 100%; margin-bottom: 10px;">';
        }
        echo '<h3>' . htmlspecialchars($s['title']) . '</h3>';
        if ($s['avg_rating'] !== null) {
            echo '<p>⭐ ' . number_format($s['avg_rating'], 1) . ' / 5</p>';
        }
        echo '<p><em>' . htmlspecialchars($s['description']) . '</em></p>';
        echo '<p><strong>' . htmlspecialchars($s['price']) . '€</strong> • Entrega: ' . htmlspecialchars($s['delivery_time']) . '</p>';
        echo '<p><small>Por <strong>' . htmlspecialchars($s['username']) . '</strong> • Categoria: ' . htmlspecialchars($s['category'] ?? '—') . '</small></p>';
        echo '<a href="service_detail.php?id=' . $s['id'] . '"><button class="primary-btn">🔍 Ver mais</button></a>';
        echo '</div>';
    }
}

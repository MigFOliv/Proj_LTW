<?php
// Mostrar erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir conexão com o banco
require_once 'includes/db.php';

// Verifica se $db existe e é um objeto PDO
if (!isset($db) || !$db instanceof PDO) {
    die("Erro: conexão com o banco de dados falhou.");
}

$query = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
$tables = $query->fetchAll(PDO::FETCH_COLUMN);

if (count($tables) === 0) {
    echo "<p><strong>Nenhuma tabela encontrada.</strong></p>";
} else {
    echo "<h3>Tabelas no banco de dados:</h3><ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
}
?>

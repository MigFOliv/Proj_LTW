<?php
// Mostrar erros para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Conexão com o banco
require_once 'includes/db.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Test DB</title></head><body>";

if (!isset($db) || !$db instanceof PDO) {
    die("<p style='color:red;'>❌ Erro: conexão com o banco de dados falhou.</p>");
}

try {
    $query = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
    $tables = $query->fetchAll(PDO::FETCH_COLUMN);

    if (count($tables) === 0) {
        echo "<p><strong>Nenhuma tabela encontrada no banco de dados.</strong></p>";
    } else {
        echo "<h3>✅ Tabelas existentes:</h3><ul>";
        foreach ($tables as $table) {
            echo "<li>" . htmlspecialchars($table) . "</li>";
        }
        echo "</ul>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red;'>Erro ao consultar o banco: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body></html>";
?>

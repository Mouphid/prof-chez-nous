<?php
require_once "config/config.php";
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL");
    echo "Column avatar added successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
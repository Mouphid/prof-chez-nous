<?php
require_once "config/config.php";
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN bio TEXT DEFAULT NULL");
    echo "Column bio added successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
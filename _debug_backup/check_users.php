<?php
require_once "config/config.php";
echo "=== User statuses ===\n";
$users = $pdo->query("SELECT id_user, name, email, role, is_active FROM users")->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $u) {
    echo "{$u['id_user']} | {$u['name']} | {$u['email']} | role: {$u['role']} | is_active: " . ($u['is_active'] === null ? 'NULL' : $u['is_active']) . "\n";
}
?>
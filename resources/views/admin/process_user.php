<?php
require_once __DIR__ . "/auth_check.php";
require_once __DIR__ . "/database_functions.php";

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if ($action == 'block') {
    execute_query("UPDATE users SET Status = 0 WHERE ID = ?", [$id]);
} elseif ($action == 'delete') {
    execute_query("DELETE FROM users WHERE ID = ?", [$id]);
}

header("Location: manage_users.php");
exit();
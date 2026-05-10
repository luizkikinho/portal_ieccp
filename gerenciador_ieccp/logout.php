<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (isset($_COOKIE['admin_token'])) {
    $sql = "UPDATE admins SET session_token = NULL WHERE session_token = :token";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['token' => $_COOKIE['admin_token']]);
}

setcookie('admin_token', '', time() - 3600, '/');

session_unset();
session_destroy();

header("Location: /gerenciador_ieccp/");
exit;

<?php
require_once __DIR__ . '/includes/sesion.php';
exigirLogin();

$id = (int)($_GET['id'] ?? 0);
if ($id > 0 && isset($_SESSION['carrito'][$id])) {
    unset($_SESSION['carrito'][$id]);
}

header('Location: carrito.php');
exit;
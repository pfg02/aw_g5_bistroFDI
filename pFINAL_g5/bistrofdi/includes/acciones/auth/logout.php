<?php
require_once __DIR__ . '/../../core/sesion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../../index.php');
    exit;
}

logoutUsuario();
header('Location: ../../../index.php');
exit;
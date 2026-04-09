<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('default_charset', 'UTF-8');
date_default_timezone_set('Europe/Madrid');

define('BASE_URL', '/aw_g5_bistroFDI/p2_g5/bistroFDI');
define('BASE_PATH', __DIR__ . '/..');

$db = new mysqli('localhost', 'bistro_user', 'bistro_pass', 'bistrofdi');

if ($db->connect_errno) {
    die('Error de conexión a la base de datos: ' . $db->connect_error);
}

if (!$db->set_charset('utf8mb4')) {
    die('Error al configurar UTF-8 en la base de datos: ' . $db->error);
}

function tienePermiso($rolRequerido): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['rol'])) {
        return false;
    }

    $jerarquia = [
        'cliente'  => 1,
        'camarero' => 2,
        'cocinero' => 3,
        'gerente'  => 4
    ];

    $rolUsuario = $_SESSION['rol'];
    $nivelUsuario = $jerarquia[$rolUsuario] ?? 0;
    $nivelRequerido = $jerarquia[$rolRequerido] ?? 5;

    return $nivelUsuario >= $nivelRequerido;
}
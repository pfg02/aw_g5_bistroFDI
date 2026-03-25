<?php

require_once __DIR__ . '/aplicacion.php';

ini_set('default_charset', 'UTF-8');
date_default_timezone_set('Europe/Madrid');

$bdDatosConexion = [
    'host' => 'localhost',
    'user' => 'bistro_user',
    'pass' => 'bistro_pass',
    'bd'   => 'bistrofdi',
];

$app = Aplicacion::getInstance();
$app->init($bdDatosConexion);

function obtenerConexionBD(): mysqli
{
    return Aplicacion::getInstance()->getConexionBd();
}

// Puente de compatibilidad con otras partes del proyecto
$db = obtenerConexionBD();

function tienePermiso($rolRequerido) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['rol'])) return false;

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
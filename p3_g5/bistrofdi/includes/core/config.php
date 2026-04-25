<?php

declare(strict_types=1);

require_once __DIR__ . '/Application.php';

ini_set('default_charset', 'UTF-8');
date_default_timezone_set('Europe/Madrid');

define('BASE_URL', '/aw_g5_bistroFDI/p3_g5/bistrofdi');
define('BASE_PATH', __DIR__ . '/../..');

$datosConexion = [
    'host' => 'localhost',
    'user' => 'bistro_user',
    'pass' => 'bistro_pass',
    'bd'   => 'bistrofdi',
];

$app = Application::getInstance();
$app->init($datosConexion, BASE_URL, BASE_PATH);

// Variable de compatibilidad para el código existente.
// La conexión real la gestiona Application.
$db = $app->conexionBd();
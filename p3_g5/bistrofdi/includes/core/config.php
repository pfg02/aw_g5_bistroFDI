<?php

declare(strict_types=1);

require_once __DIR__ . '/Application.php';

ini_set('default_charset', 'UTF-8');
date_default_timezone_set('Europe/Madrid');

/*
 * Ruta física del proyecto.
 * Este config.php está en includes/core/,
 * por eso subimos dos niveles.
 */
define('BASE_PATH', realpath(__DIR__ . '/../..'));

/*
 * Ruta web del proyecto calculada automáticamente desde htdocs.
 * Así no depende de si el proyecto está en:
 * /aw_g5_bistroFDI
 * /aw_g5_bistroFDI/p3_g5
 * /aw_g5_bistroFDI/p3_g5/bistrofdi
 */
$documentRoot = realpath($_SERVER['DOCUMENT_ROOT']);
$basePath = BASE_PATH;

$documentRoot = str_replace('\\', '/', $documentRoot);
$basePath = str_replace('\\', '/', $basePath);

$baseUrl = str_replace($documentRoot, '', $basePath);
$baseUrl = '/' . trim($baseUrl, '/');

define('BASE_URL', $baseUrl === '/' ? '' : $baseUrl);

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
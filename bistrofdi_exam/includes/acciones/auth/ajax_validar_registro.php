<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../integracion/UsuarioDAO.php';

header('Content-Type: application/json; charset=utf-8');

$campo = filter_input(INPUT_POST, 'campo', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$valor = filter_input(INPUT_POST, 'valor', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

$campo = trim((string) $campo);
$valor = trim((string) $valor);

$respuesta = [
    'ok' => false,
    'mensaje' => 'Petición no válida.'
];

if ($campo === '' || $valor === '') {
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit();
}

$conn = Application::getInstance()->conexionBd();
$usuarioDAO = new UsuarioDAO($conn);

if ($campo === 'email') {
    if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) {
        $respuesta = [
            'ok' => false,
            'mensaje' => 'El email no tiene un formato válido.'
        ];
    } elseif ($usuarioDAO->existeEmail($valor)) {
        $respuesta = [
            'ok' => false,
            'mensaje' => 'Este email ya está registrado.'
        ];
    } else {
        $respuesta = [
            'ok' => true,
            'mensaje' => 'Email disponible.'
        ];
    }

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit();
}

if ($campo === 'nombre_usuario') {
    if (strlen($valor) < 3) {
        $respuesta = [
            'ok' => false,
            'mensaje' => 'El nombre de usuario debe tener al menos 3 caracteres.'
        ];
    } elseif ($usuarioDAO->existeNombreUsuario($valor)) {
        $respuesta = [
            'ok' => false,
            'mensaje' => 'Este nombre de usuario ya está en uso.'
        ];
    } else {
        $respuesta = [
            'ok' => true,
            'mensaje' => 'Nombre de usuario disponible.'
        ];
    }

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit();
}

echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
exit();
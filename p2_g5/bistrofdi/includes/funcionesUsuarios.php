<?php

require_once __DIR__ . '/negocio/UsuarioController.php';

function buscarUsuarioPorId(int $id): ?UsuarioDTO
{
    $controller = new UsuarioController();
    return $controller->obtenerUsuarioPorId($id);
}

function loginUsuario(string $nombreUsuario, string $password): bool
{
    $controller = new UsuarioController();
    [$ok, $mensaje] = $controller->procesarLogin([
        'nombre_usuario' => $nombreUsuario,
        'password' => $password
    ]);
    return $ok;
}
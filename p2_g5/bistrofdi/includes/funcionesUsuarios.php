<?php

require_once __DIR__ . '/clases/RepositorioUsuarios.php';

function buscarUsuarioPorId(int $id): ?Usuario
{
    $repo = new RepositorioUsuarios();
    return $repo->buscarPorId($id);
}

function buscarUsuarioPorNombreUsuario(string $nombreUsuario): ?Usuario
{
    $repo = new RepositorioUsuarios();
    return $repo->buscarPorNombreUsuario($nombreUsuario);
}

function loginUsuario(string $nombreUsuario, string $password): bool
{
    $repo = new RepositorioUsuarios();
    $usuario = $repo->autenticar($nombreUsuario, $password);

    if (!$usuario) {
        return false;
    }

    $usuario->iniciarSesion();
    return true;
}
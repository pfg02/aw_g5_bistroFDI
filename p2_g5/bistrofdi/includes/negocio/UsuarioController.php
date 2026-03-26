<?php

require_once __DIR__ . '/UsuarioService.php';

class UsuarioController
{
    private UsuarioService $usuarioService;

    public function __construct()
    {
        $this->usuarioService = new UsuarioService();
    }

    public function procesarRegistro(array $post): array
    {
        return $this->usuarioService->registrarUsuario(
            $post['nombre_usuario'] ?? '',
            $post['email'] ?? '',
            $post['nombre'] ?? '',
            $post['apellidos'] ?? '',
            $post['password'] ?? '',
            $post['password2'] ?? ''
        );
    }

    public function procesarLogin(array $post): array
    {
        [$ok, $mensaje, $usuario] = $this->usuarioService->autenticarUsuario(
            $post['nombre_usuario'] ?? '',
            $post['password'] ?? ''
        );

        if ($ok && $usuario) {
            $_SESSION['id_usuario'] = $usuario->getId();
            $_SESSION['nombre_usuario'] = $usuario->getNombreUsuario();
            $_SESSION['rol'] = $usuario->getRol();
            $_SESSION['avatar'] = $usuario->getAvatar();
        }

        return [$ok, $mensaje];
    }

    public function procesarPerfil(int $idUsuario, array $post): array
    {
        return $this->usuarioService->actualizarPerfil(
            $idUsuario,
            $post['email'] ?? '',
            $post['nombre'] ?? '',
            $post['apellidos'] ?? ''
        );
    }

    public function procesarCambioRol(int $idUsuarioObjetivo, int $idUsuarioSesion, array $post): array
    {
        return $this->usuarioService->cambiarRol(
            $idUsuarioObjetivo,
            $post['rol'] ?? '',
            $idUsuarioSesion
        );
    }

    public function procesarBorrado(int $idUsuarioObjetivo, int $idUsuarioSesion): array
    {
        return $this->usuarioService->borrarUsuario($idUsuarioObjetivo, $idUsuarioSesion);
    }

    public function procesarCambioAvatar(int $idUsuario, string $rutaAvatar): array
    {
        return $this->usuarioService->actualizarAvatar($idUsuario, $rutaAvatar);
    }

    public function obtenerUsuarioPorId(int $idUsuario): ?UsuarioDTO
    {
        return $this->usuarioService->buscarUsuarioPorId($idUsuario);
    }

    public function obtenerListaUsuarios(): array
    {
        return $this->usuarioService->listarUsuarios();
    }

    public function procesarSolicitudRecuperacion(array $post): array
    {
    return $this->usuarioService->solicitarRecuperacionPassword(
        $post['email'] ?? ''
    );
    }
}
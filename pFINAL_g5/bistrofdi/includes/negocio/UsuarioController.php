<?php

require_once __DIR__ . '/UsuarioService.php';

class UsuarioController
{
    private UsuarioService $usuarioService;

    public function __construct()
    {
        $this->usuarioService = new UsuarioService();
    }

    // Procesa los datos recibidos desde el formulario de registro.
    // El controlador recoge valores del POST y delega la validación en la capa de servicio.
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

    // Procesa el inicio de sesión.
    // Si la autenticación es correcta, guarda en sesión los datos mínimos necesarios.
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

    // Procesa la actualización de datos básicos del perfil.
    // La lógica de validación y persistencia se mantiene en el servicio.
    public function procesarPerfil(int $idUsuario, array $post): array
    {
        return $this->usuarioService->actualizarPerfil(
            $idUsuario,
            $post['email'] ?? '',
            $post['nombre'] ?? '',
            $post['apellidos'] ?? ''
        );
    }

	public function actualizarVIP(int $idUsuario, int $vip): bool
    {
		return $this->usuarioService->actualizarVIP($idUsuario, $vip);
	}

    // Procesa cambios administrativos sobre un usuario.
    // El servicio comprueba permisos, restricciones y reglas de negocio.
    public function procesarCambioRol(int $idUsuarioObjetivo, int $idUsuarioSesion, array $post): array
    {
        return $this->usuarioService->cambiarRol(
            $idUsuarioObjetivo,
            $post['rol'] ?? '',
            $idUsuarioSesion
        );
    }

    // Procesa el borrado de un usuario.
    // Se envía también el usuario de sesión para evitar acciones no permitidas.
    public function procesarBorrado(int $idUsuarioObjetivo, int $idUsuarioSesion): array
    {
        return $this->usuarioService->borrarUsuario($idUsuarioObjetivo, $idUsuarioSesion);
    }

    // Procesa el cambio de avatar.
    // La ruta del archivo ya debe venir calculada desde la acción correspondiente.
    public function procesarCambioAvatar(int $idUsuario, string $rutaAvatar): array
    {
        return $this->usuarioService->actualizarAvatar($idUsuario, $rutaAvatar);
    }

    // Procesa la solicitud de recuperación de contraseña.
    // El servicio se encarga de comprobar el email y preparar la operación.
    public function procesarSolicitudRecuperacion(array $post): array
    {
        return $this->usuarioService->solicitarRecuperacionPassword(
            $post['email'] ?? ''
        );
    }

    // Obtiene un usuario concreto por identificador.
    // Útil para cargar formularios de edición o mostrar datos detallados.
    public function obtenerUsuarioPorId(int $idUsuario): ?UsuarioDTO
    {
        return $this->usuarioService->buscarUsuarioPorId($idUsuario);
    }

    // Obtiene el listado general de usuarios.
    // Si una vista necesita datos relacionados, se puede añadir un método específico
    // en servicio y DAO sin cargar SQL directamente en la vista.
    public function obtenerListaUsuarios(): array
    {
        return $this->usuarioService->listarUsuarios();
    }

    // Patrón para ampliaciones de administración:
    // 1. Recibir datos desde una vista o acción POST.
    // 2. Validar identificadores básicos.
    // 3. Delegar reglas de negocio en el servicio.
    // 4. Devolver resultado para mostrar mensaje o redirigir.
    // 5. Evitar consultas SQL dentro del controlador.
}
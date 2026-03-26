<?php

require_once __DIR__ . '/../integracion/UsuarioDAO.php';

class UsuarioService
{
    private UsuarioDAO $usuarioDAO;

    public function __construct()
    {
        $this->usuarioDAO = new UsuarioDAO();
    }

    public function registrarUsuario(
        string $nombreUsuario,
        string $email,
        string $nombre,
        string $apellidos,
        string $password,
        string $password2
    ): array {
        $nombreUsuario = trim($nombreUsuario);
        $email = trim($email);
        $nombre = trim($nombre);
        $apellidos = trim($apellidos);

        if ($nombreUsuario === '' || $email === '' || $nombre === '' || $apellidos === '' || $password === '' || $password2 === '') {
            return [false, 'Todos los campos son obligatorios.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [false, 'El email no tiene un formato válido.'];
        }

        if ($password !== $password2) {
            return [false, 'Las contraseñas no coinciden.'];
        }

        if ($this->usuarioDAO->buscarPorNombreUsuario($nombreUsuario)) {
            return [false, 'Ese nombre de usuario ya existe.'];
        }

        if ($this->usuarioDAO->existeEmail($email)) {
            return [false, 'Ese email ya está registrado.'];
        }

        $usuario = new UsuarioDTO(
            null,
            $nombreUsuario,
            $email,
            $nombre,
            $apellidos,
            password_hash($password, PASSWORD_DEFAULT),
            'cliente',
            'img/avatares/default.png'
        );

        if (!$this->usuarioDAO->insertar($usuario)) {
            return [false, 'No se ha podido registrar el usuario.'];
        }

        return [true, 'Usuario registrado correctamente.'];
    }

    public function autenticarUsuario(string $nombreUsuario, string $password): array
    {
        $nombreUsuario = trim($nombreUsuario);

        if ($nombreUsuario === '' || $password === '') {
            return [false, 'Debes rellenar usuario y contraseña.', null];
        }

        $usuario = $this->usuarioDAO->buscarPorNombreUsuario($nombreUsuario);

        if (!$usuario || !$usuario->verificarPassword($password)) {
            return [false, 'Usuario no encontrado o contraseña incorrecta.', null];
        }

        return [true, '', $usuario];
    }

    public function actualizarPerfil(int $idUsuario, string $email, string $nombre, string $apellidos): array
    {
        $email = trim($email);
        $nombre = trim($nombre);
        $apellidos = trim($apellidos);

        if ($email === '' || $nombre === '' || $apellidos === '') {
            return [false, 'Todos los campos son obligatorios.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [false, 'El email no tiene un formato válido.'];
        }

        $usuario = $this->usuarioDAO->buscarPorId($idUsuario);
        if (!$usuario) {
            return [false, 'Usuario no encontrado.'];
        }

        if ($this->usuarioDAO->existeEmail($email, $idUsuario)) {
            return [false, 'Ese email ya está en uso por otro usuario.'];
        }

        $usuario->setEmail($email);
        $usuario->setNombre($nombre);
        $usuario->setApellidos($apellidos);

        if (!$this->usuarioDAO->actualizar($usuario)) {
            return [false, 'No se ha podido actualizar el perfil.'];
        }

        return [true, 'Perfil actualizado correctamente.'];
    }

    public function cambiarRol(int $idUsuario, string $nuevoRol, int $idUsuarioSesion): array
    {
        $rolesValidos = ['cliente', 'camarero', 'cocinero', 'gerente'];

        if (!in_array($nuevoRol, $rolesValidos, true)) {
            return [false, 'Rol no válido.'];
        }

        $usuario = $this->usuarioDAO->buscarPorId($idUsuario);
        if (!$usuario) {
            return [false, 'Usuario no encontrado.'];
        }

        if ($idUsuario === $idUsuarioSesion && $nuevoRol !== 'gerente') {
            return [false, 'No puedes quitarte a ti mismo el rol de gerente.'];
        }

        if (!$this->usuarioDAO->actualizarRol($idUsuario, $nuevoRol)) {
            return [false, 'No se ha podido cambiar el rol.'];
        }

        return [true, 'Rol actualizado correctamente.'];
    }

    public function borrarUsuario(int $idUsuario, int $idUsuarioSesion): array
    {
        if ($idUsuario === $idUsuarioSesion) {
            return [false, 'No puedes borrarte a ti mismo.'];
        }

        $usuario = $this->usuarioDAO->buscarPorId($idUsuario);
        if (!$usuario) {
            return [false, 'Usuario no encontrado.'];
        }

        if (!$this->usuarioDAO->borrar($idUsuario)) {
            return [false, 'No se ha podido borrar el usuario.'];
        }

        return [true, 'Usuario borrado correctamente.'];
    }

    public function actualizarAvatar(int $idUsuario, string $rutaAvatar): array
    {
        $usuario = $this->usuarioDAO->buscarPorId($idUsuario);
        if (!$usuario) {
            return [false, 'Usuario no encontrado.'];
        }

        if (!$this->usuarioDAO->actualizarAvatar($idUsuario, $rutaAvatar)) {
            return [false, 'No se ha podido actualizar el avatar.'];
        }

        return [true, 'Avatar actualizado correctamente.'];
    }

    public function buscarUsuarioPorId(int $id): ?UsuarioDTO
    {
        return $this->usuarioDAO->buscarPorId($id);
    }

    public function listarUsuarios(): array
    {
        return $this->usuarioDAO->listarTodos();
    }

    public function solicitarRecuperacionPassword(string $email): array
    {
    $email = trim($email);

    if ($email === '') {
        return [false, 'Debes introducir un correo electrónico.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [false, 'El correo electrónico no tiene un formato válido.'];
    }

    $usuario = $this->usuarioDAO->buscarPorEmail($email);

    if ($usuario) {
    }

    return [true, 'Correo de recuperación de contraseña enviado: Revise su bandeja de entrada'];
    }

}
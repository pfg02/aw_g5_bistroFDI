<?php

require_once __DIR__ . '/../integracion/UsuarioDAO.php';

class UsuarioService
{
    private UsuarioDAO $usuarioDAO;

    public function __construct(?mysqli $db = null)
    {
        // El servicio crea el DAO usando la conexión principal de la aplicación.
        // También permite recibir una conexión externa si se necesita reutilizar en pruebas o acciones concretas.
        $this->usuarioDAO = new UsuarioDAO($db ?? Application::getInstance()->conexionBd());
    }

    // Registro de usuario.
    // Flujo habitual:
    // 1. Limpiar datos recibidos.
    // 2. Validar campos obligatorios.
    // 3. Comprobar formatos y duplicados.
    // 4. Crear DTO.
    // 5. Delegar inserción en el DAO.
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

        // Si se añaden nuevos datos al registro, deben incorporarse al DTO,
        // al formulario, a la validación y al método insertar() del DAO.
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

    // Autenticación de usuario.
    // El servicio comprueba credenciales y devuelve el usuario al controlador si son correctas.
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

    // Actualización de perfil.
    // Mantiene separada la validación de datos personales y la persistencia en base de datos.
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

        // Al editar, se excluye el propio usuario para permitir conservar su email actual.
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

    // Cambio de rol desde administración.
    // Aquí se validan valores permitidos y restricciones antes de actualizar.
    public function cambiarRol(int $idUsuario, string $nuevoRol, int $idUsuarioSesion): array
    {
        $rolesValidos = ['cliente', 'camarero', 'cocinero', 'gerente'];
        $nuevoRol = trim($nuevoRol);

        if ($idUsuario <= 0) {
            return [false, 'Usuario no válido.'];
        }

        if (!in_array($nuevoRol, $rolesValidos, true)) {
            return [false, 'Rol no válido.'];
        }

        $usuario = $this->usuarioDAO->buscarPorId($idUsuario);
        if (!$usuario) {
            return [false, 'Usuario no encontrado.'];
        }

        // Restricción para evitar que el usuario administrador se quite permisos a sí mismo.
        if ($idUsuario === $idUsuarioSesion && $nuevoRol !== 'gerente') {
            return [false, 'No puedes quitarte a ti mismo el rol de gerente.'];
        }

        if (!$this->usuarioDAO->actualizarRol($idUsuario, $nuevoRol)) {
            return [false, 'No se ha podido cambiar el rol.'];
        }

        return [true, 'Rol actualizado correctamente.'];
    }

    // Borrado de usuario.
    // Se comprueba que el identificador sea válido y que no sea el propio usuario en sesión.
    public function borrarUsuario(int $idUsuario, int $idUsuarioSesion): array
    {
        if ($idUsuario <= 0) {
            return [false, 'Usuario no válido.'];
        }

        if ($idUsuario === $idUsuarioSesion) {
            return [false, 'No puedes borrarte a ti mismo.'];
        }

        $usuario = $this->usuarioDAO->buscarPorId($idUsuario);
        if (!$usuario) {
            return [false, 'Usuario no encontrado.'];
        }

        // Si existen tablas relacionadas, revisar antes si procede borrar, bloquear o actualizar relaciones.
        if (!$this->usuarioDAO->borrar($idUsuario)) {
            return [false, 'No se ha podido borrar el usuario.'];
        }

        return [true, 'Usuario borrado correctamente.'];
    }

    // Actualización de avatar.
    // La ruta debe validarse para evitar guardar rutas externas o no esperadas.
    public function actualizarAvatar(int $idUsuario, string $rutaAvatar): array
    {
        $rutaAvatar = trim($rutaAvatar);

        if ($idUsuario <= 0) {
            return [false, 'Usuario no válido.'];
        }

        if (!$this->esRutaAvatarValida($rutaAvatar)) {
            return [false, 'La ruta del avatar no es válida.'];
        }

        $usuario = $this->usuarioDAO->buscarPorId($idUsuario);
        if (!$usuario) {
            return [false, 'Usuario no encontrado.'];
        }

        if (!$this->usuarioDAO->actualizarAvatar($idUsuario, $rutaAvatar)) {
            return [false, 'No se ha podido actualizar el avatar.'];
        }

        return [true, 'Avatar actualizado correctamente.'];
    }

    // Solicitud de recuperación de contraseña.
    // Se mantiene un mensaje genérico para no revelar si el correo existe.
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
            // Aquí iría el envío real del correo.
            // Para la práctica mostramos el flujo correcto sin revelar si existe o no.
        }

        return [true, 'Correo de recuperación de contraseña enviado: Revise su bandeja de entrada'];
    }

    // Obtiene un usuario concreto para vistas de detalle o edición.
    public function buscarUsuarioPorId(int $id): ?UsuarioDTO
    {
        return $this->usuarioDAO->buscarPorId($id);
    }

    // Listado general de usuarios.
    // Para listados con datos relacionados, crear un método específico en DAO y exponerlo desde aquí.
    public function listarUsuarios(): array
    {
        return $this->usuarioDAO->listarTodos();
    }

    // Validación de rutas internas para avatar.
    // Limita los archivos a una carpeta concreta y extensiones permitidas.
    private function esRutaAvatarValida(string $rutaAvatar): bool
    {
        return preg_match('/^img\/avatares\/[a-zA-Z0-9._-]+\.(png|jpg|jpeg|webp|gif)$/i', $rutaAvatar) === 1;
    }

    // Patrón para ampliaciones con datos asociados a usuarios:
    // 1. Crear método en DAO para consultar opciones disponibles.
    // 2. Crear método en DAO para guardar la opción asociada.
    // 3. Crear método en DAO con JOIN si se necesita mostrar información relacionada.
    // 4. Exponer esos métodos desde el servicio.
    // 5. Mantener validaciones de negocio en esta clase.
}
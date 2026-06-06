<?php

require_once __DIR__ . '/../negocio/UsuarioDTO.php';

class UsuarioDAO
{
    private mysqli $conn;

    public function __construct(mysqli $db)
    {
        $this->conn = $db;
    }

    // Búsqueda principal por identificador.
    // Se usa cuando una vista o controlador necesita cargar un usuario concreto.
    public function buscarPorId(int $id): ?UsuarioDTO
    {
        $stmt = $this->conn->prepare('SELECT * FROM usuarios WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();

        $res = $stmt->get_result();
        $fila = $res->fetch_assoc();
        $res->free();

        $stmt->close();

        return $fila ? UsuarioDTO::crearDesdeFila($fila) : null;
    }

    // Búsqueda por nombre de usuario.
    // Útil para login, registro y comprobaciones de duplicados.
    public function buscarPorNombreUsuario(string $nombreUsuario): ?UsuarioDTO
    {
        $stmt = $this->conn->prepare('SELECT * FROM usuarios WHERE nombre_usuario = ?');
        $stmt->bind_param('s', $nombreUsuario);
        $stmt->execute();

        $res = $stmt->get_result();
        $fila = $res->fetch_assoc();
        $res->free();

        $stmt->close();

        return $fila ? UsuarioDTO::crearDesdeFila($fila) : null;
    }

    // Búsqueda por email.
    // Se utiliza para validar que no existan cuentas repetidas.
    public function buscarPorEmail(string $email): ?UsuarioDTO
    {
        $stmt = $this->conn->prepare('SELECT * FROM usuarios WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();

        $res = $stmt->get_result();
        $fila = $res->fetch_assoc();
        $res->free();

        $stmt->close();

        return $fila ? UsuarioDTO::crearDesdeFila($fila) : null;
    }

    // Comprobación de email existente.
    // El parámetro opcional permite excluir el propio usuario cuando se edita un perfil.
    public function existeEmail(string $email, ?int $idExcluir = null): bool
    {
        if ($idExcluir !== null) {
            $stmt = $this->conn->prepare('SELECT id FROM usuarios WHERE email = ? AND id <> ? LIMIT 1');
            $stmt->bind_param('si', $email, $idExcluir);
        } else {
            $stmt = $this->conn->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
            $stmt->bind_param('s', $email);
        }

        $stmt->execute();

        $res = $stmt->get_result();
        $existe = (bool) $res->fetch_assoc();
        $res->free();

        $stmt->close();

        return $existe;
    }

    // Comprobación de nombre de usuario existente.
    // Mantiene la misma estructura que existeEmail para reutilizar el patrón.
    public function existeNombreUsuario(string $nombreUsuario, ?int $idExcluir = null): bool
    {
        if ($idExcluir !== null) {
            $stmt = $this->conn->prepare('SELECT id FROM usuarios WHERE nombre_usuario = ? AND id <> ? LIMIT 1');
            $stmt->bind_param('si', $nombreUsuario, $idExcluir);
        } else {
            $stmt = $this->conn->prepare('SELECT id FROM usuarios WHERE nombre_usuario = ? LIMIT 1');
            $stmt->bind_param('s', $nombreUsuario);
        }

        $stmt->execute();

        $res = $stmt->get_result();
        $existe = (bool) $res->fetch_assoc();
        $res->free();

        $stmt->close();

        return $existe;
    }

    // Inserción de un usuario completo.
    // Si se añaden nuevos campos obligatorios a usuarios, deben añadirse aquí,
    // en el DTO y en el formulario correspondiente.
    public function insertar(UsuarioDTO $usuario): bool
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO usuarios (nombre_usuario, email, nombre, apellidos, password_hash, rol, avatar)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );

        $nombreUsuario = $usuario->getNombreUsuario();
        $email = $usuario->getEmail();
        $nombre = $usuario->getNombre();
        $apellidos = $usuario->getApellidos();
        $passwordHash = $usuario->getPasswordHash();
        $rol = $usuario->getRol();
        $avatar = $usuario->getAvatar();

        $stmt->bind_param('sssssss', $nombreUsuario, $email, $nombre, $apellidos, $passwordHash, $rol, $avatar);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    // Actualización de datos generales del usuario.
    // Mantener aquí solo los campos editables desde administración o perfil.
    public function actualizar(UsuarioDTO $usuario): bool
    {
        $stmt = $this->conn->prepare(
            'UPDATE usuarios SET email = ?, nombre = ?, apellidos = ?, rol = ?, avatar = ? WHERE id = ?'
        );

        $email = $usuario->getEmail();
        $nombre = $usuario->getNombre();
        $apellidos = $usuario->getApellidos();
        $rol = $usuario->getRol();
        $avatar = $usuario->getAvatar();
        $id = $usuario->getId();

        $stmt->bind_param('sssssi', $email, $nombre, $apellidos, $rol, $avatar, $id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    // Actualización específica del rol.
    // Es preferible separar cambios concretos para evitar modificar datos no necesarios.
    public function actualizarRol(int $idUsuario, string $rol): bool
    {
        $stmt = $this->conn->prepare('UPDATE usuarios SET rol = ? WHERE id = ?');
        $stmt->bind_param('si', $rol, $idUsuario);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    // Actualización específica del avatar.
    public function actualizarAvatar(int $idUsuario, string $avatar): bool
    {
        $stmt = $this->conn->prepare('UPDATE usuarios SET avatar = ? WHERE id = ?');
        $stmt->bind_param('si', $avatar, $idUsuario);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    // Actualización específica de contraseña.
    // El hash debe venir ya calculado desde la capa de negocio.
    public function actualizarPassword(int $idUsuario, string $passwordHash): bool
    {
        $stmt = $this->conn->prepare('UPDATE usuarios SET password_hash = ? WHERE id = ?');
        $stmt->bind_param('si', $passwordHash, $idUsuario);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    // Borrado por identificador.
    // Revisar dependencias antes de borrar si existen tablas relacionadas.
    public function borrar(int $idUsuario): bool
    {
        $stmt = $this->conn->prepare('DELETE FROM usuarios WHERE id = ?');
        $stmt->bind_param('i', $idUsuario);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    // Listado general.
    // Si se necesitan datos relacionados, usar una consulta específica con JOIN
    // para no sobrecargar este método básico.
    public function listarTodos(): array
    {
        $stmt = $this->conn->prepare('SELECT * FROM usuarios ORDER BY id');
        $stmt->execute();

        $res = $stmt->get_result();
        $usuarios = [];

        while ($fila = $res->fetch_assoc()) {
            $usuarios[] = UsuarioDTO::crearDesdeFila($fila);
        }

        $res->free();
        $stmt->close();

        return $usuarios;
    }

    // Patrón para ampliaciones con datos auxiliares:
    // 1. Crear un método que liste las opciones disponibles.
    // 2. Crear un método que actualice la opción asociada al usuario.
    // 3. Crear un método con LEFT JOIN para mostrar usuarios junto con esos datos.
    // 4. Dejar las consultas SQL en el DAO y no en la vista.
}
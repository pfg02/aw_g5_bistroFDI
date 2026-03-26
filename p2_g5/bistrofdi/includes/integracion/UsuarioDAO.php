<?php

require_once __DIR__ . '/../negocio/UsuarioDTO.php';
require_once __DIR__ . '/../config.php';

class UsuarioDAO
{
    private mysqli $conn;

    public function __construct()
    {
        global $db;
        $this->conn = $db;
    }

    public function buscarPorId(int $id): ?UsuarioDTO
    {
        $stmt = $this->conn->prepare('SELECT * FROM usuarios WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $fila = $res->fetch_assoc();
        $stmt->close();

        return $fila ? UsuarioDTO::crearDesdeFila($fila) : null;
    }

    public function buscarPorNombreUsuario(string $nombreUsuario): ?UsuarioDTO
    {
        $stmt = $this->conn->prepare('SELECT * FROM usuarios WHERE nombre_usuario = ?');
        $stmt->bind_param('s', $nombreUsuario);
        $stmt->execute();
        $res = $stmt->get_result();
        $fila = $res->fetch_assoc();
        $stmt->close();

        return $fila ? UsuarioDTO::crearDesdeFila($fila) : null;
    }

    public function buscarPorEmail(string $email): ?UsuarioDTO
    {
        $stmt = $this->conn->prepare('SELECT * FROM usuarios WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        $fila = $res->fetch_assoc();
        $stmt->close();

        return $fila ? UsuarioDTO::crearDesdeFila($fila) : null;
    }

    public function existeEmail(string $email, ?int $idExcluir = null): bool
    {
        if ($idExcluir !== null) {
            $stmt = $this->conn->prepare('SELECT id FROM usuarios WHERE email = ? AND id <> ?');
            $stmt->bind_param('si', $email, $idExcluir);
        } else {
            $stmt = $this->conn->prepare('SELECT id FROM usuarios WHERE email = ?');
            $stmt->bind_param('s', $email);
        }

        $stmt->execute();
        $res = $stmt->get_result();
        $existe = (bool)$res->fetch_assoc();
        $stmt->close();

        return $existe;
    }

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

    public function actualizarRol(int $idUsuario, string $rol): bool
    {
        $stmt = $this->conn->prepare('UPDATE usuarios SET rol = ? WHERE id = ?');
        $stmt->bind_param('si', $rol, $idUsuario);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function actualizarAvatar(int $idUsuario, string $avatar): bool
    {
        $stmt = $this->conn->prepare('UPDATE usuarios SET avatar = ? WHERE id = ?');
        $stmt->bind_param('si', $avatar, $idUsuario);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function borrar(int $idUsuario): bool
    {
        $stmt = $this->conn->prepare('DELETE FROM usuarios WHERE id = ?');
        $stmt->bind_param('i', $idUsuario);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function listarTodos(): array
    {
        $res = $this->conn->query('SELECT * FROM usuarios ORDER BY id');
        $usuarios = [];

        while ($fila = $res->fetch_assoc()) {
            $usuarios[] = UsuarioDTO::crearDesdeFila($fila);
        }

        $res->free();
        return $usuarios;
    }

    public function actualizarPassword(int $idUsuario, string $passwordHash): bool
    {
    $stmt = $this->conn->prepare('UPDATE usuarios SET password_hash = ? WHERE id = ?');
    $stmt->bind_param('si', $passwordHash, $idUsuario);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
    }
}
<?php

require_once __DIR__ . '/../aplicacion.php';
require_once __DIR__ . '/Usuario.php';

class RepositorioUsuarios
{
    private mysqli $conn;

    public function __construct()
    {
        $this->conn = Aplicacion::getInstance()->getConexionBd();
    }

    public function buscarPorId(int $id): ?Usuario
    {
        $stmt = $this->conn->prepare('SELECT * FROM usuarios WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $fila = $res->fetch_assoc();
        $stmt->close();

        return $fila ? Usuario::crearDesdeFila($fila) : null;
    }

    public function buscarPorNombreUsuario(string $nombreUsuario): ?Usuario
    {
        $stmt = $this->conn->prepare('SELECT * FROM usuarios WHERE nombre_usuario = ?');
        $stmt->bind_param('s', $nombreUsuario);
        $stmt->execute();
        $res = $stmt->get_result();
        $fila = $res->fetch_assoc();
        $stmt->close();

        return $fila ? Usuario::crearDesdeFila($fila) : null;
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

    public function registrar(string $nombreUsuario, string $email, string $nombre, string $apellidos, string $password): bool
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $avatar = 'img/avatares/default.png';
        $rol = 'cliente';

        $stmt = $this->conn->prepare(
            'INSERT INTO usuarios (nombre_usuario, email, nombre, apellidos, password_hash, rol, avatar)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('sssssss', $nombreUsuario, $email, $nombre, $apellidos, $passwordHash, $rol, $avatar);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function autenticar(string $nombreUsuario, string $password): ?Usuario
    {
        $usuario = $this->buscarPorNombreUsuario($nombreUsuario);
        if ($usuario && $usuario->verificarPassword($password)) {
            return $usuario;
        }
        return null;
    }

    public function actualizarPerfil(Usuario $usuario): bool
{
    $id = $usuario->getId();
    $email = $usuario->getEmail();
    $nombre = $usuario->getNombre();
    $apellidos = $usuario->getApellidos();
    $rol = $usuario->getRol();

    $stmt = $this->conn->prepare('UPDATE usuarios SET email = ?, nombre = ?, apellidos = ?, rol = ? WHERE id = ?');
    $stmt->bind_param('ssssi', $email, $nombre, $apellidos, $rol, $id);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

    /** @return Usuario[] */
    public function listarTodos(): array
    {
        $res = $this->conn->query('SELECT * FROM usuarios ORDER BY id');
        $usuarios = [];

        while ($fila = $res->fetch_assoc()) {
            $usuarios[] = Usuario::crearDesdeFila($fila);
        }

        $res->free();
        return $usuarios;
    }

    public function cambiarRol(int $idUsuario, string $nuevoRol): bool
    {
        $rolesValidos = ['cliente', 'camarero', 'cocinero', 'gerente'];
        if (!in_array($nuevoRol, $rolesValidos, true)) {
            return false;
        }

        $stmt = $this->conn->prepare('UPDATE usuarios SET rol = ? WHERE id = ?');
        $stmt->bind_param('si', $nuevoRol, $idUsuario);
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
}
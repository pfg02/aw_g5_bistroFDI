<?php
// includes/funcionesUsuarios.php
require_once __DIR__ . '/config.php';

function obtenerUsuarioPorId(int $id): ?array {
    $conn = obtenerConexionBD();
    $sql = "SELECT * FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $usuario = $res->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $usuario ?: null;
}

function obtenerUsuarioPorNombre(string $nombreUsuario): ?array {
    $conn = obtenerConexionBD();
    $sql = "SELECT * FROM usuarios WHERE nombre_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nombreUsuario);
    $stmt->execute();
    $res = $stmt->get_result();
    $usuario = $res->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $usuario ?: null;
}

/**
 * Registra un nuevo usuario con rol 'cliente'.
 * Devuelve [true, null] si OK, o [false, mensajeError] si algo va mal.
 */
function registrarUsuario(string $nombreUsuario, string $email, string $nombre,
                          string $apellidos, string $password, string $password2): array {
    if ($password !== $password2) {
        return [false, "Las contraseñas no coinciden."];
    }

    if (obtenerUsuarioPorNombre($nombreUsuario)) {
        return [false, "Ese nombre de usuario ya existe."];
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $conn = obtenerConexionBD();
    $sql = "INSERT INTO usuarios (nombre_usuario, email, nombre, apellidos, password_hash, rol, avatar)
            VALUES (?, ?, ?, ?, ?, 'cliente', 'img/avatares/default.png')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $nombreUsuario, $email, $nombre, $apellidos, $passwordHash);

    $ok = $stmt->execute();
    $error = $ok ? null : ("Error al registrar usuario: " . $stmt->error);

    $stmt->close();
    $conn->close();

    return [$ok, $error];
}

/** Actualiza email, nombre y apellidos del usuario */
function actualizarPerfil(int $id, string $email, string $nombre, string $apellidos): bool {
    $conn = obtenerConexionBD();
    $sql = "UPDATE usuarios SET email=?, nombre=?, apellidos=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $email, $nombre, $apellidos, $id);
    $ok = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $ok;
}

/** Cambia la ruta del avatar de un usuario */
function actualizarAvatar(int $id, string $rutaAvatar): bool {
    $conn = obtenerConexionBD();
    $sql = "UPDATE usuarios SET avatar=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $rutaAvatar, $id);
    $ok = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $ok;
}

/** Cambia el rol de un usuario (cliente, camarero, cocinero, gerente) */
function cambiarRolUsuario(int $id, string $nuevoRol): bool {
    $rolesValidos = ['cliente','camarero','cocinero','gerente'];
    if (!in_array($nuevoRol, $rolesValidos, true)) return false;

    $conn = obtenerConexionBD();
    $sql = "UPDATE usuarios SET rol=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nuevoRol, $id);
    $ok = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $ok;
}

/** Devuelve todos los usuarios (para que el gerente los gestione) */
function listarUsuarios(): array {
    $conn = obtenerConexionBD();
    $sql = "SELECT id, nombre_usuario, email, nombre, apellidos, rol, avatar FROM usuarios ORDER BY id";
    $res = $conn->query($sql);
    $usuarios = [];
    while ($fila = $res->fetch_assoc()) {
        $usuarios[] = $fila;
    }
    $res->free();
    $conn->close();
    return $usuarios;
}

/** Permite borrar usuarios (para que el gerente los gestione) */
function borrarUsuario(int $id): bool {
    $conn = obtenerConexionBD();
    $sql = "DELETE FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $ok = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $ok;
}
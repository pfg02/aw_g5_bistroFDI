<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/negocio/UsuarioController.php';

exigirLogin();

$controller = new UsuarioController();
$mensaje = '';
$mensajeError = '';

$usuario = $controller->obtenerUsuarioPorId((int)$_SESSION['id_usuario']);

if (!$usuario) {
    echo '<p>Usuario no encontrado.</p>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$ok, $texto] = $controller->procesarPerfil((int)$_SESSION['id_usuario'], $_POST);

    if ($ok) {
        $mensaje = $texto;
        $usuario = $controller->obtenerUsuarioPorId((int)$_SESSION['id_usuario']);
    } else {
        $mensajeError = $texto;
        $usuario->setEmail(trim($_POST['email'] ?? $usuario->getEmail()));
        $usuario->setNombre(trim($_POST['nombre'] ?? $usuario->getNombre()));
        $usuario->setApellidos(trim($_POST['apellidos'] ?? $usuario->getApellidos()));
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi perfil - Bistro FDI</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include __DIR__ . '/includes/vistas/comun/nav.php'; ?>

    <h1>Mi perfil</h1>

    <?php if ($mensaje): ?>
        <p style="color:green;"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <?php if ($mensajeError): ?>
        <p style="color:red;"><?= htmlspecialchars($mensajeError) ?></p>
    <?php endif; ?>

    <p><strong>Avatar actual:</strong></p>
    <img src="<?= htmlspecialchars($usuario->getAvatar()) ?>" alt="Avatar" width="120">

    <p><a href="cambiarAvatar.php">Cambiar avatar</a></p>

    <form method="post" action="perfil.php">
        <label>Nombre de usuario:
            <input type="text" value="<?= htmlspecialchars($usuario->getNombreUsuario()) ?>" disabled>
        </label><br><br>

        <label>Email:
            <input type="email" name="email" value="<?= htmlspecialchars($usuario->getEmail()) ?>" required>
        </label><br><br>

        <label>Nombre:
            <input type="text" name="nombre" value="<?= htmlspecialchars($usuario->getNombre()) ?>" required>
        </label><br><br>

        <label>Apellidos:
            <input type="text" name="apellidos" value="<?= htmlspecialchars($usuario->getApellidos()) ?>" required>
        </label><br><br>

        <button type="submit">Guardar cambios</button>
    </form>

    <p><a href="index.php">Volver al inicio</a></p>
</body>
</html>
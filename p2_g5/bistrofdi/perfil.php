<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/funcionesUsuarios.php';

exigirLogin(); // solo usuarios logueados

$idUsuario = $_SESSION['id_usuario'];
$usuario   = obtenerUsuarioPorId($idUsuario);

$mensajeOk = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email     = trim($_POST['email'] ?? '');
    $nombre    = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');

    if (actualizarPerfil($idUsuario, $email, $nombre, $apellidos)) {
        $mensajeOk = 'Perfil actualizado correctamente.';
        $usuario   = obtenerUsuarioPorId($idUsuario); // recargar datos
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

<?php include __DIR__ . '/includes/nav.php'; ?>
    <h1>Mi perfil</h1>

    <?php if ($mensajeOk): ?>
        <p style="color:green;"><?php echo htmlspecialchars($mensajeOk); ?></p>
    <?php endif; ?>

    <p><strong>Nombre de usuario:</strong> <?php echo htmlspecialchars($usuario['nombre_usuario']); ?></p>
    <p><strong>Rol:</strong> <?php echo htmlspecialchars($usuario['rol']); ?></p>

    <p>
        <strong>Avatar actual:</strong><br>
        <img src="<?php echo htmlspecialchars($usuario['avatar']); ?>" alt="Avatar" width="120"><br>
        <a href="cambiarAvatar.php">Cambiar avatar</a>
    </p>

    <h2>Datos personales</h2>
    <form method="post" action="perfil.php">
        <label>Email:
            <input type="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
        </label><br><br>

        <label>Nombre:
            <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
        </label><br><br>

        <label>Apellidos:
            <input type="text" name="apellidos" value="<?php echo htmlspecialchars($usuario['apellidos']); ?>" required>
        </label><br><br>

        <button type="submit">Guardar cambios</button>
    </form>

    <p><a href="index.php">Volver al inicio</a></p>
    <p><a href="logout.php">Cerrar sesión</a></p>
</body>
</html>
<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/negocio/UsuarioController.php';

exigirRol('gerente');

$controller = new UsuarioController();
$mensaje = '';
$mensajeError = '';

$idUsuario = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$usuario = $controller->obtenerUsuarioPorId($idUsuario);

if (!$usuario) {
    echo '<p>Usuario no encontrado.</p>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$ok, $texto] = $controller->procesarCambioRol($idUsuario, (int)$_SESSION['id_usuario'], $_POST);

    if ($ok) {
        header('Location: gestionarUsuarios.php');
        exit;
    }

    $mensajeError = $texto;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cambiar rol - Bistro FDI</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include __DIR__ . '/includes/vistas/comun/nav.php'; ?>

    <h1>Cambiar rol de usuario</h1>

    <?php if ($mensaje): ?>
        <p style="color:green;"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <?php if ($mensajeError): ?>
        <p style="color:red;"><?= htmlspecialchars($mensajeError) ?></p>
    <?php endif; ?>

    <form method="post" action="cambiarRol.php?id=<?= urlencode((string)$idUsuario) ?>">
        <p><strong>Usuario:</strong> <?= htmlspecialchars($usuario->getNombreUsuario()) ?></p>

        <label>Nuevo rol:
            <select name="rol" required>
                <?php
                $roles = ['cliente', 'camarero', 'cocinero', 'gerente'];
                $rolActual = $_POST['rol'] ?? $usuario->getRol();
                foreach ($roles as $rol):
                ?>
                    <option value="<?= htmlspecialchars($rol) ?>" <?= $rol === $rolActual ? 'selected' : '' ?>>
                        <?= htmlspecialchars(ucfirst($rol)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label><br><br>

        <button type="submit">Guardar rol</button>
    </form>

    <p><a href="gestionarUsuarios.php">Volver a gestión de usuarios</a></p>
</body>
</html>
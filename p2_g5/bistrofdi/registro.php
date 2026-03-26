<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/negocio/UsuarioController.php';

$controller = new UsuarioController();

$mensajeError = '';
$datosFormulario = [
    'nombre_usuario' => '',
    'email' => '',
    'nombre' => '',
    'apellidos' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datosFormulario['nombre_usuario'] = trim($_POST['nombre_usuario'] ?? '');
    $datosFormulario['email'] = trim($_POST['email'] ?? '');
    $datosFormulario['nombre'] = trim($_POST['nombre'] ?? '');
    $datosFormulario['apellidos'] = trim($_POST['apellidos'] ?? '');

    [$ok, $mensaje] = $controller->procesarRegistro($_POST);

    if ($ok) {
        header('Location: login.php?registro=ok');
        exit;
    }

    $mensajeError = $mensaje;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - Bistro FDI</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include __DIR__ . '/includes/vistas/comun/nav.php'; ?>

    <h1>Registrarse</h1>

    <?php if ($mensajeError): ?>
        <p style="color:red;"><?= htmlspecialchars($mensajeError) ?></p>
    <?php endif; ?>

    <form method="post" action="registro.php">
        <label>Nombre de usuario:
            <input type="text" name="nombre_usuario" value="<?= htmlspecialchars($datosFormulario['nombre_usuario']) ?>" required>
        </label><br><br>

        <label>Email:
            <input type="email" name="email" value="<?= htmlspecialchars($datosFormulario['email']) ?>" required>
        </label><br><br>

        <label>Nombre:
            <input type="text" name="nombre" value="<?= htmlspecialchars($datosFormulario['nombre']) ?>" required>
        </label><br><br>

        <label>Apellidos:
            <input type="text" name="apellidos" value="<?= htmlspecialchars($datosFormulario['apellidos']) ?>" required>
        </label><br><br>

        <label>Contraseña:
            <input type="password" name="password" required>
        </label><br><br>

        <label>Repetir contraseña:
            <input type="password" name="password2" required>
        </label><br><br>

        <button type="submit">Crear cuenta</button>
    </form>

    <p><a href="index.php">Volver al inicio</a></p>
    <p><a href="login.php">Ir a iniciar sesión</a></p>
</body>
</html>
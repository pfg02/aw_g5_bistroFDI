<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/funcionesUsuarios.php';

$mensajeError = '';

$datosFormulario = [
    'nombre_usuario' => '',
    'email' => '',
    'nombre' => '',
    'apellidos' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datosFormulario['nombre_usuario'] = trim($_POST['nombre_usuario'] ?? '');
    $datosFormulario['email']          = trim($_POST['email'] ?? '');
    $datosFormulario['nombre']         = trim($_POST['nombre'] ?? '');
    $datosFormulario['apellidos']      = trim($_POST['apellidos'] ?? '');
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    list($ok, $error) = registrarUsuario(
        $datosFormulario['nombre_usuario'],
        $datosFormulario['email'],
        $datosFormulario['nombre'],
        $datosFormulario['apellidos'],
        $password,
        $password2
    );

    if ($ok) {
        header('Location: login.php?registro=ok');
        exit;
    }

    $mensajeError = $error;
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
        <p style="color:red;"><?php echo htmlspecialchars($mensajeError); ?></p>
    <?php endif; ?>

    <form method="post" action="registro.php">
        <label>Nombre de usuario:
            <input type="text" name="nombre_usuario"
                   value="<?php echo htmlspecialchars($datosFormulario['nombre_usuario']); ?>" required>
        </label><br><br>

        <label>Email:
            <input type="email" name="email"
                   value="<?php echo htmlspecialchars($datosFormulario['email']); ?>" required>
        </label><br><br>

        <label>Nombre:
            <input type="text" name="nombre"
                   value="<?php echo htmlspecialchars($datosFormulario['nombre']); ?>" required>
        </label><br><br>

        <label>Apellidos:
            <input type="text" name="apellidos"
                   value="<?php echo htmlspecialchars($datosFormulario['apellidos']); ?>" required>
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
<?php
require_once __DIR__ . '/includes/sesion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bistro FDI - Inicio</title>
</head>
<body>
    <h1>Bistro FDI - Funcionalidad 0</h1>

    <?php if (usuarioLogueado()): ?>
        <p>Hola, <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?>.</p>

        <ul>
            <li><a href="perfil.php">Mi perfil</a></li>

            <?php if (tieneRolMinimo('gerente')): ?>
                <li><a href="gestionarUsuarios.php">Gestionar usuarios</a></li>
            <?php endif; ?>

            <li><a href="logout.php">Cerrar sesión</a></li>
        </ul>

    <?php else: ?>
        <p>No has iniciado sesión.</p>
        <ul>
            <li><a href="login.php">Iniciar sesión</a></li>
            <li><a href="registro.php">Registrarse</a></li>
        </ul>
    <?php endif; ?>
</body>
</html>
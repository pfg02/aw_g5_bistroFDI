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

ob_start();
?>
<section class="f0-auth-wrap">
    <div class="f0-auth-card">
        <div class="f0-auth-body">
            <h1 class="f0-auth-title">Registrarse</h1>
            <div class="f0-auth-divider"></div>

            <?php if ($mensajeError): ?>
                <div class="f0-msg-error"><?= htmlspecialchars($mensajeError) ?></div>
            <?php endif; ?>

            <form method="post" action="registro.php" class="f0-form">
                <label>
                    Nombre de usuario
                    <input type="text" name="nombre_usuario" value="<?= htmlspecialchars($datosFormulario['nombre_usuario']) ?>" placeholder="Nombre de usuario" required>
                </label>

                <label>
                    Email
                    <input type="email" name="email" value="<?= htmlspecialchars($datosFormulario['email']) ?>" placeholder="Correo electrónico" required>
                </label>

                <label>
                    Nombre
                    <input type="text" name="nombre" value="<?= htmlspecialchars($datosFormulario['nombre']) ?>" placeholder="Nombre" required>
                </label>

                <label>
                    Apellidos
                    <input type="text" name="apellidos" value="<?= htmlspecialchars($datosFormulario['apellidos']) ?>" placeholder="Apellidos" required>
                </label>

                <label>
                    Contraseña
                    <input type="password" name="password" placeholder="Contraseña" required>
                </label>

                <label>
                    Repetir contraseña
                    <input type="password" name="password2" placeholder="Repetir contraseña" required>
                </label>

                <div class="f0-form-actions">
                    <button type="submit" class="f0-btn">Crear cuenta</button>
                </div>
            </form>

            <div class="f0-auth-switch">
                <p><a href="login.php">Ir a iniciar sesión</a></p>
                <p><a href="index.php">Volver al inicio</a></p>
            </div>
        </div>
    </div>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
$tituloPagina = 'Registro - Bistro FDI';
$bodyClass = 'f0-body';

require __DIR__ . '/includes/vistas/comun/plantilla.php';
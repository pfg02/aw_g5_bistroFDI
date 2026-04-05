<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/negocio/UsuarioController.php';

$controller = new UsuarioController();

$mensaje = '';
$mensajeError = '';
$datosFormulario = ['email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datosFormulario['email'] = trim($_POST['email'] ?? '');

    [$ok, $texto] = $controller->procesarSolicitudRecuperacion($_POST);

    if ($ok) {
        $mensaje = $texto;
        $datosFormulario['email'] = '';
    } else {
        $mensajeError = $texto;
    }
}

ob_start();
?>
<section class="f0-auth-wrap">
    <div class="f0-auth-card">
        <div class="f0-auth-body">
            <h1 class="f0-auth-title">Recuperar contraseña</h1>
            <div class="f0-auth-divider"></div>
            <p class="f0-auth-text">Introduce tu email para recibir la recuperación de contraseña.</p>

            <?php if ($mensaje): ?>
                <div class="f0-msg-ok"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>

            <?php if ($mensajeError): ?>
                <div class="f0-msg-error"><?= htmlspecialchars($mensajeError) ?></div>
            <?php endif; ?>

            <form method="post" action="olvidoPassword.php" class="f0-form">
                <label>
                    Email
                    <input type="email" name="email" value="<?= htmlspecialchars($datosFormulario['email']) ?>" placeholder="Correo electrónico" required>
                </label>

                <div class="f0-form-actions">
                    <button type="submit" class="f0-btn">Enviar correo de recuperación</button>
                </div>
            </form>

            <div class="f0-auth-switch">
                <p><a href="login.php">Volver a iniciar sesión</a></p>
                <p><a href="index.php">Volver al inicio</a></p>
            </div>
        </div>
    </div>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
$tituloPagina = 'Recuperar contraseña - Bistro FDI';
$bodyClass = 'f0-body';

require __DIR__ . '/includes/vistas/comun/plantilla.php';
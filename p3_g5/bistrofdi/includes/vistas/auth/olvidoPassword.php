<?php
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../negocio/UsuarioController.php';
require_once __DIR__ . '/../../formularios/formularioOlvidoPassword.php';

$controller = new UsuarioController();
$formulario = new FormularioOlvidoPassword($controller);
$htmlFormulario = $formulario->gestiona();
$mensaje = $formulario->getMensajeExito();

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

            <?= $htmlFormulario ?>

            <div class="f0-auth-switch">
                <p><a href="login.php">Volver a iniciar sesión</a></p>
                <p><a href="../../../index.php">Volver al inicio</a></p>
            </div>
        </div>
    </div>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
$tituloPagina = 'Recuperar contraseña - Bistro FDI';
$bodyClass = 'f0-body';

require __DIR__ . '/../partials/plantilla.php';
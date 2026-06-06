<?php
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../negocio/UsuarioController.php';
require_once __DIR__ . '/../../formularios/formularioLogin.php';

if (usuarioLogueado()) {
    header('Location: ../../../index.php');
    exit;
}

$controller = new UsuarioController();
$formulario = new FormularioLogin($controller);
$htmlFormulario = $formulario->gestiona();

ob_start();
?>
<section class="f0-auth-wrap">
    <div class="f0-auth-card">
        <div class="f0-auth-body">
            <h1 class="f0-auth-title">Iniciar sesión</h1>
            <div class="f0-auth-divider"></div>

            <?php if (isset($_GET['registro']) && $_GET['registro'] === 'ok'): ?>
                <div class="f0-msg-ok">Usuario registrado correctamente. Ya puedes iniciar sesión.</div>
            <?php endif; ?>

            <?= $htmlFormulario ?>

            <div class="f0-auth-switch">
                <p><a href="registro.php">Registrarse</a></p>
                <p><a href="olvidoPassword.php">He olvidado mi contraseña</a></p>
                <p><a href="../../../index.php">Volver al inicio</a></p>
            </div>
        </div>
    </div>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
$tituloPagina = 'Iniciar sesión - Bistro FDI';
$bodyClass = 'f0-body';

require __DIR__ . '/../partials/plantilla.php';
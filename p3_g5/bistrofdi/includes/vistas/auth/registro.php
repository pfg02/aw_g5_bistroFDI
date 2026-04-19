<?php
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../negocio/UsuarioController.php';
require_once __DIR__ . '/../../formularios/formularioRegistro.php';

$controller = new UsuarioController();
$formulario = new FormularioRegistro($controller);
$htmlFormulario = $formulario->gestiona();

ob_start();
?>
<section class="f0-auth-wrap">
    <div class="f0-auth-card">
        <div class="f0-auth-body">
            <h1 class="f0-auth-title">Registrarse</h1>
            <div class="f0-auth-divider"></div>

            <?= $htmlFormulario ?>

            <div class="f0-auth-switch">
                <p><a href="login.php">Ir a iniciar sesión</a></p>
                <p><a href="../../../index.php">Volver al inicio</a></p>
            </div>
        </div>
    </div>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
$tituloPagina = 'Registro - Bistro FDI';
$bodyClass = 'f0-body';

require __DIR__ . '/../partials/plantilla.php';
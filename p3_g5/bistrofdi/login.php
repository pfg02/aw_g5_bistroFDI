<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/negocio/UsuarioController.php';

if (usuarioLogueado()) {
    header('Location: index.php');
    exit;
}

$controller = new UsuarioController();
$mensajeError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$ok, $mensaje] = $controller->procesarLogin($_POST);

    if ($ok) {
        header('Location: index.php');
        exit;
    }

    $mensajeError = $mensaje;
}

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

            <?php if ($mensajeError): ?>
                <div class="f0-msg-error"><?= htmlspecialchars($mensajeError) ?></div>
            <?php endif; ?>

            <form method="post" action="login.php" class="f0-form">
                <label>
                    Nombre de usuario
                    <input type="text" name="nombre_usuario" value="<?= htmlspecialchars($_POST['nombre_usuario'] ?? '') ?>" placeholder="Nombre de usuario" required>
                </label>

                <label>
                    Contraseña
                    <input type="password" name="password" placeholder="Contraseña" required>
                </label>

                <div class="f0-form-actions">
                    <button type="submit" class="f0-btn">Entrar</button>
                </div>
            </form>

            <div class="f0-auth-switch">
                <p><a href="registro.php">Registrarse</a></p>
                <p><a href="olvidoPassword.php">He olvidado mi contraseña</a></p>
                <p><a href="index.php">Volver al inicio</a></p>
            </div>
        </div>
    </div>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
$tituloPagina = 'Iniciar sesión - Bistro FDI';
$bodyClass = 'f0-body';

require __DIR__ . '/includes/vistas/comun/plantilla.php';
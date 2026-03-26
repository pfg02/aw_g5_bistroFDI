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
<section class="contenedor-principal">
    <h1>Recuperar contraseña</h1>

    <?php if ($mensaje): ?>
        <p style="color:green;"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <?php if ($mensajeError): ?>
        <p style="color:red;"><?= htmlspecialchars($mensajeError) ?></p>
    <?php endif; ?>

    <form method="post" action="olvidoPassword.php">
        <label>Email:
            <input type="email" name="email" value="<?= htmlspecialchars($datosFormulario['email']) ?>" required>
        </label><br><br>

        <button type="submit">Enviar correo de recuperación</button>
    </form>

    <p><a href="login.php">Volver a iniciar sesión</a></p>
    <p><a href="index.php">Volver al inicio</a></p>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
$tituloPagina = 'Recuperar contraseña - Bistro FDI';

require __DIR__ . '/includes/vistas/comun/plantilla.php';
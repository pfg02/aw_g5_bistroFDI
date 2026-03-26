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
<section class="contenedor-principal">
    <h1>Iniciar sesión</h1>

    <?php if (isset($_GET['registro']) && $_GET['registro'] === 'ok'): ?>
        <p style="color:green;">Usuario registrado correctamente. Ya puedes iniciar sesión.</p>
    <?php endif; ?>

    <?php if ($mensajeError): ?>
        <p style="color:red;"><?= htmlspecialchars($mensajeError) ?></p>
    <?php endif; ?>

    <form method="post" action="login.php">
        <label>Nombre de usuario:
            <input type="text" name="nombre_usuario" value="<?= htmlspecialchars($_POST['nombre_usuario'] ?? '') ?>" required>
        </label><br><br>

        <label>Contraseña:
            <input type="password" name="password" required>
        </label><br><br>

        <button type="submit">Entrar</button>
    </form>

    <p><a href="index.php">Volver al inicio</a></p>
    <p><a href="registro.php">Registrarse</a></p>
    <p><a href="olvidoPassword.php">He olvidado mi contraseña</a></p>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
$tituloPagina = 'Iniciar sesión - Bistro FDI';

require __DIR__ . '/includes/vistas/comun/plantilla.php';
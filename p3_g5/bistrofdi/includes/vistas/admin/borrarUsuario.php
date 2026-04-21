<?php
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../negocio/UsuarioController.php';

exigirRol('gerente');

$controller = new UsuarioController();
$mensajeError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idUsuario = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT) ?: 0;
} else {
    $idUsuario = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
}

if ($idUsuario <= 0) {
    header('Location: gestionarUsuarios.php');
    exit;
}

$usuario = $controller->obtenerUsuarioPorId($idUsuario);

if (!$usuario) {
    die('Usuario no encontrado.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$ok, $texto] = $controller->procesarBorrado($idUsuario, (int) $_SESSION['id_usuario']);

    if ($ok) {
        header('Location: gestionarUsuarios.php');
        exit;
    }

    $mensajeError = $texto;
}

ob_start();
?>
<section class="f0-page">
    <h1 class="f0-page-title">Borrar usuario</h1>

    <div class="f0-page-content">
        <div class="f0-confirm-box">
            <?php if ($mensajeError): ?>
                <div class="f0-msg-error"><?= htmlspecialchars($mensajeError) ?></div>
            <?php endif; ?>

            <p class="f0-confirm-text">
                ¿Seguro que quieres borrar al usuario
                <strong><?= htmlspecialchars($usuario->getNombreUsuario()) ?></strong>?
            </p>

            <form method="post" action="borrarUsuario.php" class="f0-form">
                <input type="hidden" name="id_usuario" value="<?= htmlspecialchars((string) $idUsuario) ?>">

                <div class="f0-form-actions" style="justify-content:center;">
                    <button type="submit" class="f0-btn-danger">Sí, borrar usuario</button>
                    <a href="gestionarUsuarios.php" class="f0-btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
$tituloPagina = 'Borrar usuario - Bistro FDI';
$bodyClass = 'f0-body';

require __DIR__ . '/../partials/plantilla.php';
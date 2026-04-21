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
    [$ok, $texto] = $controller->procesarCambioRol($idUsuario, (int) $_SESSION['id_usuario'], $_POST);

    if ($ok) {
        header('Location: gestionarUsuarios.php');
        exit;
    }

    $mensajeError = $texto;
}

$roles = ['cliente', 'camarero', 'cocinero', 'gerente'];
$rolActual = $_POST['rol'] ?? $usuario->getRol();

$descripciones = [
    'cliente' => 'Acceso como cliente habitual y gestión de sus pedidos.',
    'camarero' => 'Permisos de sala y gestión de entrega/cobro.',
    'cocinero' => 'Permisos de preparación de pedidos en cocina.',
    'gerente' => 'Control total del sistema y de usuarios.'
];

ob_start();
?>
<section class="f0-page">
    <h1 class="f0-page-title">Cambiar rol de usuario</h1>

    <div class="f0-page-content">
        <?php if ($mensajeError): ?>
            <div class="f0-msg-error"><?= htmlspecialchars($mensajeError) ?></div>
        <?php endif; ?>

        <div class="f0-card-soft" style="margin-bottom:18px;">
            <p><strong>Usuario:</strong> <?= htmlspecialchars($usuario->getNombreUsuario()) ?></p>
            <p><strong>Rol actual:</strong> <?= htmlspecialchars($usuario->getRol()) ?></p>
        </div>

        <form method="post" action="cambiarRol.php" class="f0-form">
            <input type="hidden" name="id_usuario" value="<?= htmlspecialchars((string) $idUsuario) ?>">

            <div class="f0-role-list">
                <?php foreach ($roles as $rol): ?>
                    <label class="f0-role-option">
                        <input type="radio" name="rol" value="<?= htmlspecialchars($rol) ?>" <?= $rol === $rolActual ? 'checked' : '' ?> required>
                        <span>
                            <strong><?= htmlspecialchars(ucfirst($rol)) ?></strong>
                            <small><?= htmlspecialchars($descripciones[$rol]) ?></small>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="f0-form-actions">
                <button type="submit" class="f0-btn">Guardar rol</button>
                <a href="gestionarUsuarios.php" class="f0-btn-secondary">Volver</a>
            </div>
        </form>
    </div>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
$tituloPagina = 'Cambiar rol - Bistro FDI';
$bodyClass = 'f0-body';

require __DIR__ . '/../partials/plantilla.php';
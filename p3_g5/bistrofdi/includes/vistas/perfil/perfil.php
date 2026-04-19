<?php
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../negocio/UsuarioController.php';
require_once __DIR__ . '/../../formularios/FormularioPerfil.php';

exigirLogin();

$controller = new UsuarioController();
$usuario = $controller->obtenerUsuarioPorId((int)$_SESSION['id_usuario']);

if (!$usuario) {
    die('Usuario no encontrado.');
}

$formulario = new FormularioPerfil($controller, (int)$_SESSION['id_usuario'], [
    'email' => $usuario->getEmail(),
    'nombre' => $usuario->getNombre(),
    'apellidos' => $usuario->getApellidos(),
], $usuario->getNombreUsuario());
$htmlFormulario = $formulario->gestiona();
$mensaje = $formulario->getMensajeExito();

$usuario = $controller->obtenerUsuarioPorId((int)$_SESSION['id_usuario']);
if (!$usuario) {
    die('Usuario no encontrado.');
}

$rolClase = 'f0-role-' . $usuario->getRol();

ob_start();
?>
<section class="f0-page">
    <h1 class="f0-page-title">Mi perfil</h1>

    <div class="f0-page-content">
        <div class="f0-profile-grid">
            <aside class="f0-profile-aside">
                <img src="<?= BASE_URL . '/' . htmlspecialchars($usuario->getAvatar()) ?>" alt="Avatar" class="f0-profile-avatar">

                <div class="f0-role-badge <?= htmlspecialchars($rolClase) ?>">
                    <?= htmlspecialchars($usuario->getRol()) ?>
                </div>

                <div class="f0-card-soft">
                    <p><strong>Usuario:</strong> <?= htmlspecialchars($usuario->getNombreUsuario()) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($usuario->getEmail()) ?></p>
                    <p><strong>Nombre:</strong> <?= htmlspecialchars($usuario->getNombre()) ?></p>
                    <p><strong>Apellidos:</strong> <?= htmlspecialchars($usuario->getApellidos()) ?></p>
                </div>

                <div class="f0-form-actions">
                    <a href="cambiarAvatar.php" class="f0-btn">Cambiar avatar</a>
                </div>
            </aside>

            <div>
                <?php if ($mensaje): ?>
                    <div class="f0-msg-ok"><?= htmlspecialchars($mensaje) ?></div>
                <?php endif; ?>

                <?= $htmlFormulario ?>
            </div>
        </div>
    </div>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
$tituloPagina = 'Mi perfil - Bistro FDI';
$bodyClass = 'f0-body';

require __DIR__ . '/../partials/plantilla.php';
?>
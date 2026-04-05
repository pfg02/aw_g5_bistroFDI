<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/negocio/UsuarioController.php';

exigirLogin();

$controller = new UsuarioController();
$mensaje = '';
$mensajeError = '';

$usuario = $controller->obtenerUsuarioPorId((int)$_SESSION['id_usuario']);

if (!$usuario) {
    die('Usuario no encontrado.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$ok, $texto] = $controller->procesarPerfil((int)$_SESSION['id_usuario'], $_POST);

    if ($ok) {
        $mensaje = $texto;
        $usuario = $controller->obtenerUsuarioPorId((int)$_SESSION['id_usuario']);
    } else {
        $mensajeError = $texto;
        $usuario->setEmail(trim($_POST['email'] ?? $usuario->getEmail()));
        $usuario->setNombre(trim($_POST['nombre'] ?? $usuario->getNombre()));
        $usuario->setApellidos(trim($_POST['apellidos'] ?? $usuario->getApellidos()));
    }
}

$rolClase = 'f0-role-' . $usuario->getRol();

ob_start();
?>
<section class="f0-page">
    <h1 class="f0-page-title">Mi perfil</h1>

    <div class="f0-page-content">
        <div class="f0-profile-grid">
            <aside class="f0-profile-aside">
                <img src="<?= htmlspecialchars($usuario->getAvatar()) ?>" alt="Avatar" class="f0-profile-avatar">

                <div class="f0-role-badge <?= htmlspecialchars($rolClase) ?>">
                    <?= htmlspecialchars($usuario->getRol()) ?>
                </div>

                <div class="f0-card-soft">
                    <p><strong>Usuario:</strong> <?= htmlspecialchars($usuario->getNombreUsuario()) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($usuario->getEmail()) ?></p>
                    <p><strong>Nombre:</strong> <?= htmlspecialchars($usuario->getNombre()) ?></p>
                    <p><strong>Apellidos:</strong> <?= htmlspecialchars($usuario->getApellidos()) ?></p>
                </div>

                <div class="f0-form-actions" style="margin-top:16px;">
                    <a href="cambiarAvatar.php" class="f0-btn">Cambiar avatar</a>
                </div>
            </aside>

            <div>
                <?php if ($mensaje): ?>
                    <div class="f0-msg-ok"><?= htmlspecialchars($mensaje) ?></div>
                <?php endif; ?>

                <?php if ($mensajeError): ?>
                    <div class="f0-msg-error"><?= htmlspecialchars($mensajeError) ?></div>
                <?php endif; ?>

                <form method="post" action="perfil.php" class="f0-form">
                    <div class="f0-form-grid">
                        <label>
                            Nombre de usuario
                            <input type="text" value="<?= htmlspecialchars($usuario->getNombreUsuario()) ?>" disabled>
                        </label>

                        <label>
                            Email
                            <input type="email" name="email" value="<?= htmlspecialchars($usuario->getEmail()) ?>" required>
                        </label>

                        <label>
                            Nombre
                            <input type="text" name="nombre" value="<?= htmlspecialchars($usuario->getNombre()) ?>" required>
                        </label>

                        <label>
                            Apellidos
                            <input type="text" name="apellidos" value="<?= htmlspecialchars($usuario->getApellidos()) ?>" required>
                        </label>
                    </div>

                    <div class="f0-form-actions">
                        <button type="submit" class="f0-btn">Guardar cambios</button>
                        <a href="index.php" class="f0-btn-secondary">Volver al inicio</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
$tituloPagina = 'Mi perfil - Bistro FDI';
$bodyClass = 'f0-body';

require __DIR__ . '/includes/vistas/comun/plantilla.php';
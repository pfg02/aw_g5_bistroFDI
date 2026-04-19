<?php
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../negocio/UsuarioController.php';

exigirLogin();

$controller = new UsuarioController();

$idUsuario = (int) $_SESSION['id_usuario'];
$usuario = $controller->obtenerUsuarioPorId($idUsuario);

if (!$usuario) {
    die('Usuario no encontrado.');
}

$mensajeOk = '';
$mensajeError = '';

$carpetaAvatares = __DIR__ . '/../../../img/avatares';
$rutaBaseAvatares = 'img/avatares';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['avatar_predefinido']) && $_POST['avatar_predefinido'] !== '') {
        $archivo = basename($_POST['avatar_predefinido']);
        $ruta = $rutaBaseAvatares . '/' . $archivo;

        [$ok, $mensaje] = $controller->procesarCambioAvatar($idUsuario, $ruta);

        if ($ok) {
            $_SESSION['avatar'] = $ruta;
            $mensajeOk = $mensaje;
        } else {
            $mensajeError = $mensaje;
        }

    } elseif (isset($_POST['accion']) && $_POST['accion'] === 'subir') {
        if (!empty($_FILES['archivo']['name'])) {
            $nombreArchivo = basename($_FILES['archivo']['name']);
            $destino = $carpetaAvatares . '/' . $nombreArchivo;
            $ruta = $rutaBaseAvatares . '/' . $nombreArchivo;

            if (move_uploaded_file($_FILES['archivo']['tmp_name'], $destino)) {
                [$ok, $mensaje] = $controller->procesarCambioAvatar($idUsuario, $ruta);

                if ($ok) {
                    $_SESSION['avatar'] = $ruta;
                    $mensajeOk = $mensaje;
                } else {
                    $mensajeError = $mensaje;
                }
            } else {
                $mensajeError = 'Error al subir la imagen.';
            }
        } else {
            $mensajeError = 'No has seleccionado ninguna imagen.';
        }

    } elseif (isset($_POST['accion']) && $_POST['accion'] === 'defecto') {
        $ruta = $rutaBaseAvatares . '/default.png';

        [$ok, $mensaje] = $controller->procesarCambioAvatar($idUsuario, $ruta);

        if ($ok) {
            $_SESSION['avatar'] = $ruta;
            $mensajeOk = $mensaje;
        } else {
            $mensajeError = $mensaje;
        }
    }

    $usuario = $controller->obtenerUsuarioPorId($idUsuario);
}

$avataresPredefinidos = [
    'camarero.png',
    'cliente.png',
    'cocinero.png',
    'gerente.png',
    'default.png'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar avatar - Bistro FDI</title>
    <link rel="stylesheet" href="../../../css/estilos.css">
</head>
<body class="f0-body">
    <?php include __DIR__ . '/../partials/nav.php'; ?>

    <main>
        <section class="f0-page">
            <h1 class="f0-page-title">Cambiar avatar</h1>

            <div class="f0-page-content">
                <div class="f0-avatar-grid">
                    <aside class="f0-avatar-aside">
                        <img src="../../../<?= htmlspecialchars($usuario->getAvatar()) ?>" alt="Avatar actual" class="f0-avatar-current">

                        <div class="f0-card-soft">
                            <p><strong>Usuario:</strong> <?= htmlspecialchars($usuario->getNombreUsuario()) ?></p>
                            <p><strong>Rol:</strong> <?= htmlspecialchars($usuario->getRol()) ?></p>
                        </div>
                    </aside>

                    <div>
                        <?php if ($mensajeError): ?>
                            <div class="f0-msg-error"><?= htmlspecialchars($mensajeError) ?></div>
                        <?php endif; ?>

                        <?php if ($mensajeOk): ?>
                            <div class="f0-msg-ok"><?= htmlspecialchars($mensajeOk) ?></div>
                        <?php endif; ?>

                        <h2>Seleccionar avatar predefinido</h2>
                        <form method="post" class="f0-form">
                            <div class="f0-avatar-gallery">
                                <?php foreach ($avataresPredefinidos as $archivo): ?>
                                    <label class="f0-avatar-option">
                                        <input type="radio" name="avatar_predefinido" value="<?= htmlspecialchars($archivo) ?>">
                                        <img src="../../../img/avatares/<?= htmlspecialchars($archivo) ?>" alt="Avatar">
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <div class="f0-form-actions">
                                <button type="submit" class="f0-btn">Guardar avatar predefinido</button>
                            </div>
                        </form>

                        <div class="f0-avatar-section">
                            <h2>Subir imagen propia</h2>
                            <form method="post" enctype="multipart/form-data" class="f0-form">
                                <input type="hidden" name="accion" value="subir">
                                <input type="file" name="archivo" accept="image/*">
                                <div class="f0-form-actions">
                                    <button type="submit" class="f0-btn">Subir y usar esta imagen</button>
                                </div>
                            </form>
                        </div>

                        <div class="f0-avatar-section">
                            <h2>Usar avatar por defecto</h2>
                            <form method="post" class="f0-form">
                                <input type="hidden" name="accion" value="defecto">
                                <div class="f0-form-actions">
                                    <button type="submit" class="f0-btn-secondary">Restaurar avatar por defecto</button>
                                    <a href="perfil.php" class="f0-btn-secondary">Volver a mi perfil</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
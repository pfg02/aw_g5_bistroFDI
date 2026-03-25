<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/clases/RepositorioUsuarios.php';
require_once __DIR__ . '/includes/formularios/FormCambiarAvatar.php';

exigirLogin();

$repo = new RepositorioUsuarios();
$usuario = $repo->buscarPorId((int)$_SESSION['id_usuario']);

if (!$usuario) {
    echo '<p>Usuario no encontrado.</p>';
    exit;
}

$formCambiarAvatar = new FormCambiarAvatar($usuario);
$htmlFormulario = $formCambiarAvatar->gestiona();
$mensajeOk = $formCambiarAvatar->getMensajeOk();

$usuario = $repo->buscarPorId((int)$_SESSION['id_usuario']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cambiar avatar - Bistro FDI</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include __DIR__ . '/includes/vistas/comun/nav.php'; ?>

    <div class="contenedor-principal">
        <h1>Cambiar avatar</h1>

        <?php if ($mensajeOk): ?>
            <p style="color:green;"><?= htmlspecialchars($mensajeOk) ?></p>
        <?php endif; ?>

        <?= $htmlFormulario ?>

        <br>
        <p><a href="perfil.php">Volver a mi perfil</a></p>
    </div>
</body>
</html>
	
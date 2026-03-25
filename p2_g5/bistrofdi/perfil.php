<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/clases/RepositorioUsuarios.php';
require_once __DIR__ . '/includes/formularios/FormPerfil.php';

exigirLogin();

$repo = new RepositorioUsuarios();
$usuario = $repo->buscarPorId((int)$_SESSION['id_usuario']);

if (!$usuario) {
    echo '<p>Usuario no encontrado.</p>';
    exit;
}

$formPerfil = new FormPerfil($usuario);
$htmlFormulario = $formPerfil->gestiona();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi perfil - Bistro FDI</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include __DIR__ . '/includes/vistas/comun/nav.php'; ?>

    <h1>Mi perfil</h1>

    <p><strong>Avatar actual:</strong></p>
    <img src="<?= htmlspecialchars($usuario->getAvatar()) ?>" alt="Avatar" width="120">

    <p><a href="cambiarAvatar.php">Cambiar avatar</a></p>

    <?= $htmlFormulario ?>

    <p><a href="index.php">Volver al inicio</a></p>
</body>
</html>
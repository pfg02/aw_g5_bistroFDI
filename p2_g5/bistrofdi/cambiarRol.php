<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/clases/RepositorioUsuarios.php';
require_once __DIR__ . '/includes/formularios/FormCambiarRol.php';

exigirRol('gerente');

$idUsuario = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$repo = new RepositorioUsuarios();
$usuario = $repo->buscarPorId($idUsuario);

if (!$usuario) {
    echo '<p>Usuario no encontrado.</p>';
    exit;
}

$formCambiarRol = new FormCambiarRol($usuario);
$htmlFormulario = $formCambiarRol->gestiona();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cambiar rol - Bistro FDI</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include __DIR__ . '/includes/vistas/comun/nav.php'; ?>

    <h1>Cambiar rol de usuario</h1>

    <?= $htmlFormulario ?>

    <p><a href="gestionarUsuarios.php">Volver a gestión de usuarios</a></p>
</body>
</html>
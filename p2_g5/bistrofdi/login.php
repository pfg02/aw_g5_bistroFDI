<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/formularios/FormLogin.php';

if (usuarioLogueado()) {
    header('Location: index.php');
    exit;
}

$formLogin = new FormLogin();
$htmlFormulario = $formLogin->gestiona();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión - Bistro FDI</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include __DIR__ . '/includes/vistas/comun/nav.php'; ?>

    <h1>Iniciar sesión</h1>

    <?= $htmlFormulario ?>

    <p><a href="index.php">Volver al inicio</a></p>
    <p><a href="registro.php">Registrarse</a></p>
    <p><a href="olvidoPassword.php">He olvidado mi contraseña</a></p>
</body>
</html>
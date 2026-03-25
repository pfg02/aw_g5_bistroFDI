<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/formularios/FormRegistro.php';

$formRegistro = new FormRegistro();
$htmlFormulario = $formRegistro->gestiona();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - Bistro FDI</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include __DIR__ . '/includes/vistas/comun/nav.php'; ?>

    <h1>Registrarse</h1>

    <?= $htmlFormulario ?>

    <p><a href="index.php">Volver al inicio</a></p>
    <p><a href="login.php">Ir a iniciar sesión</a></p>
</body>
</html>
<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/clases/RepositorioUsuarios.php';

exigirRol('gerente');

$repo = new RepositorioUsuarios();
$usuarios = $repo->listarTodos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar usuarios - Bistro FDI</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include __DIR__ . '/includes/vistas/comun/nav.php'; ?>

    <h1>Gestión de usuarios</h1>

    <?php include __DIR__ . '/includes/vistas/usuarios/tablaUsuarios.php'; ?>

    <p><a href="index.php">Volver al inicio</a></p>
</body>
</html>
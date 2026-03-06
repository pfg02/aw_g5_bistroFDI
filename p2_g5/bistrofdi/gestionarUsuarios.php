<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/funcionesUsuarios.php';

exigirLogin();
exigirRol('gerente');

$usuarios = listarUsuarios();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar usuarios - Bistro FDI</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<?php include __DIR__ . '/includes/nav.php'; ?>

<h1>Gestión de usuarios</h1>

<table border="1" cellpadding="6">
    <tr>
        <th>ID</th>
        <th>Nombre usuario</th>
        <th>Email</th>
        <th>Nombre</th>
        <th>Apellidos</th>
        <th>Rol</th>
        <th>Acciones</th>
    </tr>

    <?php foreach ($usuarios as $u): ?>
        <tr>
            <td><?php echo htmlspecialchars($u['id']); ?></td>
            <td><?php echo htmlspecialchars($u['nombre_usuario']); ?></td>
            <td><?php echo htmlspecialchars($u['email']); ?></td>
            <td><?php echo htmlspecialchars($u['nombre']); ?></td>
            <td><?php echo htmlspecialchars($u['apellidos']); ?></td>
            <td><?php echo htmlspecialchars($u['rol']); ?></td>
            <td>
                <a href="cambiarRol.php?id=<?php echo $u['id']; ?>">Cambiar rol</a>

                 <?php
                // Solo mostramos "Borrar" si NO es el usuario logueado (no borrar al gerente a sí mismo)
                $idSesion = $_SESSION['id_usuario'] ?? 0;
                if ((int)$u['id'] !== (int)$idSesion): ?>
        |
                <a href="borrarUsuario.php?id=<?php echo $u['id']; ?>"
                onclick="return confirm('¿Seguro que quieres borrar este usuario?');">
                Borrar
                </a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>

</table>

<br>
<a href="index.php">Volver al inicio</a>

</body>
</html>
<?php
// cambiarRol.php: vista para cambiar el rol de un usuario (solo gerente)
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/funcionesUsuarios.php';

exigirLogin();
exigirRol('gerente');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$usuario = obtenerUsuarioPorId($id);

if (!$usuario) {
    echo "<p>Usuario no encontrado.</p>";
    echo '<p><a href="gestionarUsuarios.php">Volver</a></p>';
    exit;
}

$mensajeOk = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevoRol = $_POST['nuevo_rol'] ?? 'cliente';

    if (cambiarRolUsuario($id, $nuevoRol)) {
        $mensajeOk = 'Rol actualizado correctamente.';
        // refrescamos datos del usuario
        $usuario = obtenerUsuarioPorId($id);
    } else {
        $mensajeOk = 'Error al actualizar el rol.';
    }
}

$roles = ['cliente', 'camarero', 'cocinero', 'gerente'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cambiar rol - Bistro FDI</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include __DIR__ . '/includes/nav.php'; ?>

    <h1>Cambiar rol de usuario</h1>

    <?php if ($mensajeOk): ?>
        <p style="color:green;"><?php echo htmlspecialchars($mensajeOk); ?></p>
    <?php endif; ?>

    <p><strong>Usuario:</strong> <?php echo htmlspecialchars($usuario['nombre_usuario']); ?></p>
    <p><strong>Nombre completo:</strong> <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']); ?></p>
    <p><strong>Rol actual:</strong> <?php echo htmlspecialchars($usuario['rol']); ?></p>

    <form method="post" action="cambiarRol.php?id=<?php echo $usuario['id']; ?>">
        <label>Nuevo rol:
            <select name="nuevo_rol">
                <?php foreach ($roles as $rol): ?>
                    <option value="<?php echo $rol; ?>"
                        <?php if ($usuario['rol'] === $rol) echo 'selected'; ?>>
                        <?php echo ucfirst($rol); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <br><br>
        <button type="submit">Guardar cambios</button>
    </form>

    <p><a href="gestionarUsuarios.php">Volver a gestión de usuarios</a></p>
</body>
</html>
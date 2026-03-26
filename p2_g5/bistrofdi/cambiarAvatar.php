<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/negocio/UsuarioController.php';

exigirLogin();

$controller = new UsuarioController();
$mensaje = '';
$mensajeError = '';

$usuario = $controller->obtenerUsuarioPorId((int)$_SESSION['id_usuario']);

if (!$usuario) {
    echo '<p>Usuario no encontrado.</p>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $carpetaAvatares = 'img/avatares';
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'predefinido') {
        $archivo = basename($_POST['avatar_predefinido'] ?? '');

        if ($archivo === '') {
            $mensajeError = 'Debes seleccionar un avatar predefinido.';
        } else {
            $ruta = $carpetaAvatares . '/' . $archivo;
            [$ok, $texto] = $controller->procesarCambioAvatar((int)$_SESSION['id_usuario'], $ruta);

            if ($ok) {
                $_SESSION['avatar'] = $ruta;
                $mensaje = $texto;
                $usuario = $controller->obtenerUsuarioPorId((int)$_SESSION['id_usuario']);
            } else {
                $mensajeError = $texto;
            }
        }
    } elseif ($accion === 'subir') {
        if (empty($_FILES['archivo']['name'])) {
            $mensajeError = 'No has seleccionado ninguna imagen.';
        } else {
            $nombreArchivo = basename($_FILES['archivo']['name']);
            $destino = $carpetaAvatares . '/' . $nombreArchivo;

            if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $destino)) {
                $mensajeError = 'Error al subir la imagen.';
            } else {
                [$ok, $texto] = $controller->procesarCambioAvatar((int)$_SESSION['id_usuario'], $destino);

                if ($ok) {
                    $_SESSION['avatar'] = $destino;
                    $mensaje = $texto;
                    $usuario = $controller->obtenerUsuarioPorId((int)$_SESSION['id_usuario']);
                } else {
                    $mensajeError = $texto;
                }
            }
        }
    } elseif ($accion === 'defecto') {
        $ruta = $carpetaAvatares . '/default.png';
        [$ok, $texto] = $controller->procesarCambioAvatar((int)$_SESSION['id_usuario'], $ruta);

        if ($ok) {
            $_SESSION['avatar'] = $ruta;
            $mensaje = $texto;
            $usuario = $controller->obtenerUsuarioPorId((int)$_SESSION['id_usuario']);
        } else {
            $mensajeError = $texto;
        }
    } else {
        $mensajeError = 'Acción no válida.';
    }
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
    <title>Cambiar avatar - Bistro FDI</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include __DIR__ . '/includes/vistas/comun/nav.php'; ?>

    <div class="contenedor-principal">
        <h1>Cambiar avatar</h1>

        <?php if ($mensaje): ?>
            <p style="color:green;"><?= htmlspecialchars($mensaje) ?></p>
        <?php endif; ?>

        <?php if ($mensajeError): ?>
            <p style="color:red;"><?= htmlspecialchars($mensajeError) ?></p>
        <?php endif; ?>

        <p>Avatar actual:</p>
        <img src="<?= htmlspecialchars($usuario->getAvatar()) ?>" alt="Avatar actual" width="120">

        <hr>

        <form method="post" action="cambiarAvatar.php" enctype="multipart/form-data">
            <h2>Seleccionar avatar predefinido</h2>

            <?php foreach ($avataresPredefinidos as $archivo): ?>
                <label style="display:inline-block; margin-right:10px;">
                    <input type="radio" name="avatar_predefinido" value="<?= htmlspecialchars($archivo) ?>">
                    <img src="img/avatares/<?= htmlspecialchars($archivo) ?>" width="80" alt="">
                </label>
            <?php endforeach; ?>

            <br><br>
            <button type="submit" name="accion" value="predefinido">Guardar avatar predefinido</button>

            <hr>

            <h2>Subir imagen propia</h2>
            <input type="file" name="archivo" accept="image/*">
            <br><br>
            <button type="submit" name="accion" value="subir">Subir y usar esta imagen</button>

            <hr>

            <h2>Usar avatar por defecto</h2>
            <button type="submit" name="accion" value="defecto">Restaurar avatar por defecto</button>
        </form>

        <br>
        <p><a href="perfil.php">Volver a mi perfil</a></p>
    </div>
</body>
</html>
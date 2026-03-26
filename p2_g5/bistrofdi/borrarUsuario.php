<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/negocio/UsuarioController.php';

exigirRol('gerente');

$controller = new UsuarioController();
$mensajeError = '';

$idUsuario = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$usuario = $controller->obtenerUsuarioPorId($idUsuario);

if (!$usuario) {
    die('Usuario no encontrado.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$ok, $texto] = $controller->procesarBorrado($idUsuario, (int)$_SESSION['id_usuario']);

    if ($ok) {
        header('Location: gestionarUsuarios.php');
        exit;
    }

    $mensajeError = $texto;
}

ob_start();
?>
<section class="contenedor-principal">
    <h1>Borrar usuario</h1>

    <?php if ($mensajeError): ?>
        <p style="color:red;"><?= htmlspecialchars($mensajeError) ?></p>
    <?php endif; ?>

    <form method="post" action="borrarUsuario.php?id=<?= urlencode((string)$idUsuario) ?>">
        <p>¿Seguro que quieres borrar al usuario <strong><?= htmlspecialchars($usuario->getNombreUsuario()) ?></strong>?</p>
        <button type="submit">Sí, borrar usuario</button>
    </form>

    <p><a href="gestionarUsuarios.php">Volver a gestión de usuarios</a></p>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
$tituloPagina = 'Borrar usuario - Bistro FDI';

require __DIR__ . '/includes/vistas/comun/plantilla.php';
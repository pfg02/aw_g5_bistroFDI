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
    [$ok, $texto] = $controller->procesarCambioRol($idUsuario, (int)$_SESSION['id_usuario'], $_POST);

    if ($ok) {
        header('Location: gestionarUsuarios.php');
        exit;
    }

    $mensajeError = $texto;
}

ob_start();
?>
<section class="contenedor-principal">
    <h1>Cambiar rol de usuario</h1>

    <?php if ($mensajeError): ?>
        <p style="color:red;"><?= htmlspecialchars($mensajeError) ?></p>
    <?php endif; ?>

    <form method="post" action="cambiarRol.php?id=<?= urlencode((string)$idUsuario) ?>">
        <p><strong>Usuario:</strong> <?= htmlspecialchars($usuario->getNombreUsuario()) ?></p>

        <label>Nuevo rol:
            <select name="rol" required>
                <?php
                $roles = ['cliente', 'camarero', 'cocinero', 'gerente'];
                $rolActual = $_POST['rol'] ?? $usuario->getRol();
                foreach ($roles as $rol):
                ?>
                    <option value="<?= htmlspecialchars($rol) ?>" <?= $rol === $rolActual ? 'selected' : '' ?>>
                        <?= htmlspecialchars(ucfirst($rol)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label><br><br>

        <button type="submit">Guardar rol</button>
    </form>

    <p><a href="gestionarUsuarios.php">Volver a gestión de usuarios</a></p>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
$tituloPagina = 'Cambiar rol - Bistro FDI';

require __DIR__ . '/includes/vistas/comun/plantilla.php';
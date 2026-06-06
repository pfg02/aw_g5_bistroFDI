<?php
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../negocio/UsuarioController.php';

// Acceso restringido a administración.
// Cualquier modificación de usuarios debe comprobar permisos antes de procesar datos.
exigirRol('gerente');

$controller = new UsuarioController();
$mensajeError = '';

// El identificador puede venir por GET al cargar la pantalla,
// o por POST cuando se envía el formulario.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idUsuario = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT) ?: 0;
} else {
    $idUsuario = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
}

// Validación mínima del identificador principal.
// Si no es válido, se vuelve a la vista de gestión.
if ($idUsuario <= 0) {
    header('Location: gestionarUsuarios.php');
    exit;
}

// Carga del registro que se va a modificar.
// La vista necesita los datos actuales para mostrarlos en el formulario.
$usuario = $controller->obtenerUsuarioPorId($idUsuario);

if (!$usuario) {
    die('Usuario no encontrado.');
}

// Procesamiento del formulario.
// La vista recoge datos, pero la lógica de validación y actualización se delega.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$ok, $texto] = $controller->procesarCambioRol($idUsuario, (int) $_SESSION['id_usuario'], $_POST);

    if ($ok) {
        header('Location: gestionarUsuarios.php');
        exit;
    }

    $mensajeError = $texto;
}

// Opciones disponibles para el formulario.
// Si las opciones vinieran de base de datos, se cargarían desde controller/service/DAO.
$roles = ['cliente', 'camarero', 'cocinero', 'gerente'];

// Mantiene seleccionado el valor enviado si hubo error,
// o el valor actual guardado si la página se carga por primera vez.
$rolActual = $_POST['rol'] ?? $usuario->getRol();

$descripciones = [
    'cliente' => 'Acceso como cliente habitual y gestión de sus pedidos.',
    'camarero' => 'Permisos de sala y gestión de entrega/cobro.',
    'cocinero' => 'Permisos de preparación de pedidos en cocina.',
    'gerente' => 'Control total del sistema y de usuarios.'
];

ob_start();
?>
<section class="f0-page">
    <h1 class="f0-page-title">Cambiar rol de usuario</h1>

    <div class="f0-page-content">
        <?php if ($mensajeError): ?>
            <div class="f0-msg-error"><?= htmlspecialchars($mensajeError) ?></div>
        <?php endif; ?>

        <div class="f0-card-soft" style="margin-bottom:18px;">
            <p><strong>Usuario:</strong> <?= htmlspecialchars($usuario->getNombreUsuario()) ?></p>
            <p><strong>Rol actual:</strong> <?= htmlspecialchars($usuario->getRol()) ?></p>
        </div>

        <?php
        // Formulario de actualización.
        // Patrón reutilizable:
        // 1. Enviar id principal en hidden.
        // 2. Enviar la opción seleccionada.
        // 3. Procesar por POST en esta misma página o en una acción separada.
        // 4. Redirigir tras guardar correctamente.
        ?>

        <form method="post" action="cambiarRol.php" class="f0-form">
            <input type="hidden" name="id_usuario" value="<?= htmlspecialchars((string) $idUsuario) ?>">

            <div class="f0-role-list">
                <?php foreach ($roles as $rol): ?>
                    <label class="f0-role-option">
                        <input type="radio" name="rol" value="<?= htmlspecialchars($rol) ?>" <?= $rol === $rolActual ? 'checked' : '' ?> required>
                        <span>
                            <strong><?= htmlspecialchars(ucfirst($rol)) ?></strong>
                            <small><?= htmlspecialchars($descripciones[$rol]) ?></small>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="f0-form-actions">
                <button type="submit" class="f0-btn">Guardar rol</button>
                <a href="gestionarUsuarios.php" class="f0-btn-secondary">Volver</a>
            </div>
        </form>
    </div>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
$tituloPagina = 'Cambiar rol - Bistro FDI';
$bodyClass = 'f0-body';

require __DIR__ . '/../partials/plantilla.php';
<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../negocio/OfertasController.php';
require_once __DIR__ . '/../../integracion/ProductoDAO.php';
require_once __DIR__ . '/../../formularios/formularioOferta.php';
require_once __DIR__ . '/../../negocio/OfertasDTO.php';

exigirLogin();
exigirRol('gerente');

$idOferta = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

if ($idOferta === false || $idOferta === null) {
    header('Location: ' . BASE_URL . '/includes/vistas/admin/gestion_ofertas.php?msg=error');
    exit();
}

$db = Application::getInstance()->conexionBd();
$ofertasController = OfertasController::getInstance($db);
$productoDAO = new ProductoDAO($db);

$productosDisponibles = $productoDAO->listarTodos();
$oferta = $ofertasController->obtenerOfertaPorId((int) $idOferta);

if ($oferta === null) {
    $_SESSION['mensaje_error'] = 'La oferta indicada no existe.';
    header('Location: ' . BASE_URL . '/includes/vistas/admin/gestion_ofertas.php?msg=error');
    exit();
}

$formulario = new FormularioOferta(
    $oferta,
    $productosDisponibles,
    BASE_URL . '/includes/vistas/admin/editar_oferta.php?id=' . urlencode((string) $idOferta)
);

$htmlFormulario = $formulario->gestiona();
$datosValidados = $formulario->getDatosValidados();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($datosValidados['oferta'])) {
   	if ($ofertasController->actualizarOferta($datosValidados['oferta'])) {
        $_SESSION['mensaje_exito'] = 'La oferta se ha actualizado correctamente.';
        header('Location: ' . BASE_URL . '/includes/vistas/admin/gestion_ofertas.php');
        exit();
    }

    $_SESSION['mensaje_error'] = 'No se pudo actualizar la oferta.';
}

$tituloPagina = 'Editar Oferta - Bistró FDI';
$bodyClass = 'f0-body';

ob_start();
?>

<div class="main-bienvenida">
    <section class="tarjeta-presentacion tarjeta-ancha">
        <h1>Editar <span>Oferta</span></h1>
        <p class="lema">Modifica productos, fechas o descuento del pack</p>
        <div class="divisor"></div>

        <?php if (isset($_SESSION['mensaje_error'])): ?>
            <div class="alerta alerta-error">
                <?= htmlspecialchars($_SESSION['mensaje_error']) ?>
                <?php unset($_SESSION['mensaje_error']); ?>
            </div>
        <?php endif; ?>

        <div class="mensaje-sesion">
            <?= $htmlFormulario ?>
        </div>
    </section>
</div>

<?php
$contenidoPrincipal = ob_get_clean();
require_once __DIR__ . '/../partials/plantilla.php';
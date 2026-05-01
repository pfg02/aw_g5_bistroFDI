<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../negocio/OfertasController.php'; 
require_once __DIR__ . '/../../integracion/ProductoDAO.php'; 
require_once __DIR__ . '/../../formularios/formularioOferta.php';

exigirLogin();
exigirRol('gerente');

$db = Application::getInstance()->conexionBd();
$ofertasController = OfertasController::getInstance($db);
$productoDAO = new ProductoDAO($db);
$productosDisponibles = $productoDAO->listarTodos();

$formulario = new FormularioOferta(
    null,
    $productosDisponibles,
    BASE_URL . '/includes/vistas/admin/crear_oferta.php'
);

$htmlFormulario = $formulario->gestiona();
$datosValidados = $formulario->getDatosValidados();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($datosValidados['oferta'])) {
    
    if ($ofertasController->crearOferta($datosValidados['oferta'])) {
        $_SESSION['mensaje_exito'] = 'La oferta se ha creado correctamente.';
        header('Location: ' . BASE_URL . '/includes/vistas/admin/gestion_ofertas.php');
        exit();
    }

    $_SESSION['mensaje_error'] = 'No se pudo crear la oferta. Revisa los datos introducidos.';
}

$tituloPagina = 'Crear Oferta - Bistró FDI';
$bodyClass = 'f0-body';

ob_start();
?>

<div class="main-bienvenida">
    <section class="tarjeta-presentacion tarjeta-ancha">
        <h1>Crear <span>Oferta</span></h1>
        <p class="lema">Define un pack de productos y su descuento</p>
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
?>
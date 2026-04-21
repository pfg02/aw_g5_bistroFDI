<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../integracion/OfertasDAO.php';
require_once __DIR__ . '/../../integracion/ProductoDAO.php';
require_once __DIR__ . '/../../formularios/FormularioOferta.php';
require_once __DIR__ . '/../../negocio/OfertaDTO.php';

exigirLogin();
exigirRol('gerente');

$idOferta = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

if ($idOferta === false || $idOferta === null) {
    header('Location: ' . BASE_URL . '/includes/vistas/admin/gestion_ofertas.php?msg=error');
    exit();
}

global $db;

$ofertaDAO = new OfertaDAO($db);
$productoDAO = new ProductoDAO();
$productosDisponibles = $productoDAO->listarTodos();

$oferta = cargarOferta($ofertaDAO, (int) $idOferta);

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
    $ok = false;

    if (method_exists($ofertaDAO, 'actualizarOferta')) {
        $ok = $ofertaDAO->actualizarOferta($datosValidados['oferta']);
    }

    if ($ok) {
        header('Location: ' . BASE_URL . '/includes/vistas/admin/gestion_ofertas.php?msg=editada');
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

function cargarOferta(OfertaDAO $ofertaDAO, int $idOferta): ?OfertaDTO
{
    if (method_exists($ofertaDAO, 'obtenerPorId')) {
        $oferta = $ofertaDAO->obtenerPorId($idOferta);
        if ($oferta instanceof OfertaDTO) {
            $productos = $ofertaDAO->obtenerProductosDeOferta($idOferta);
            $oferta->setProductos(normalizarProductosOferta($productos));
            return $oferta;
        }
    }

    if (method_exists($ofertaDAO, 'listarOfertas')) {
        $ofertas = $ofertaDAO->listarOfertas();

        foreach ($ofertas as $oferta) {
            $id = null;

            if (is_object($oferta) && method_exists($oferta, 'getId')) {
                $id = $oferta->getId();
            } elseif (is_array($oferta)) {
                $id = $oferta['id'] ?? null;
            }

            if ((int) $id === $idOferta && $oferta instanceof OfertaDTO) {
                $productos = $ofertaDAO->obtenerProductosDeOferta($idOferta);
                $oferta->setProductos(normalizarProductosOferta($productos));
                return $oferta;
            }
        }
    }

    return null;
}

function normalizarProductosOferta(array $productos): array
{
    $resultado = [];

    foreach ($productos as $producto) {
        $resultado[] = [
            'producto_id' => (int) ($producto['producto_id'] ?? 0),
            'cantidad' => (int) ($producto['cantidad'] ?? 1),
        ];
    }

    return $resultado;
}
?>
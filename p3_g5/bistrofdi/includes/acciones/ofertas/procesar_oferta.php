<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../integracion/OfertasDAO.php';
require_once __DIR__ . '/../../negocio/PedidoServiceApp.php';

exigirLogin();
exigirRol('cliente');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../vistas/tienda/carrito.php');
    exit();
}

$accion = $_POST['accion'] ?? '';
$idOferta = filter_input(INPUT_POST, 'id_oferta', FILTER_VALIDATE_INT);

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}
if (!isset($_SESSION['ofertas_aplicadas'])) {
    $_SESSION['ofertas_aplicadas'] = [];
}

// Acción: Quitar una oferta ya aplicada
if ($accion === 'quitar') {
    if ($idOferta && isset($_SESSION['ofertas_aplicadas'][$idOferta])) {
        unset($_SESSION['ofertas_aplicadas'][$idOferta]);
        $_SESSION['mensaje_exito'] = 'La oferta ha sido retirada de tu carrito.';
    }
    header('Location: ../../vistas/tienda/carrito.php');
    exit();
}

// Acción: Aplicar una nueva oferta
if ($accion === 'aplicar') {
    if (!$idOferta) {
        $_SESSION['mensaje_error'] = 'Por favor, selecciona una oferta válida.';
        header('Location: ../../vistas/tienda/carrito.php');
        exit();
    }

    if (empty($_SESSION['carrito'])) {
        $_SESSION['mensaje_error'] = 'No puedes aplicar ofertas a un carrito vacío.';
        header('Location: ../../vistas/tienda/carrito.php');
        exit();
    }

    // Si ya la tiene aplicada, avisamos
    if (isset($_SESSION['ofertas_aplicadas'][$idOferta])) {
        $_SESSION['mensaje_error'] = 'Ya has aplicado esta oferta. El sistema ya ha calculado las máximas veces posibles.';
        header('Location: ../../vistas/tienda/carrito.php');
        exit();
    }

    $db = Application::getInstance()->conexionBd();
    $ofertaDAO = new OfertaDAO($db);
    $pedidoService = new PedidoServiceApp($db);

    $oferta = $ofertaDAO->obtenerPorId($idOferta);

    if (!$oferta || !$ofertaDAO->esOfertaActiva($oferta)) {
        $_SESSION['mensaje_error'] = 'La oferta seleccionada no existe o ha caducado.';
        header('Location: ../../vistas/tienda/carrito.php');
        exit();
    }

    // Cargar los productos que requiere el pack en el DTO
    $productosRequeridos = $ofertaDAO->obtenerProductosDeOferta($idOferta);
    $oferta->setProductos($productosRequeridos);

    $resultado = $pedidoService->calcularDescuentoOferta($_SESSION['carrito'], $_SESSION['ofertas_aplicadas'], $oferta);

    if ($resultado['exito']) {
        $_SESSION['ofertas_aplicadas'][$idOferta] = [
            'nombre' => $oferta->getNombre(),
            'veces_aplicada' => $resultado['veces_aplicada'],
            'descuento' => $resultado['descuento'],
            'productos_requeridos' => $resultado['productos_requeridos'] // Para el cálculo secuencial futuro
        ];
        $_SESSION['mensaje_exito'] = $resultado['mensaje'];
    } else {
        $_SESSION['mensaje_error'] = $resultado['mensaje'];
    }

    header('Location: ../../vistas/tienda/carrito.php');
    exit();
}

header('Location: ../../vistas/tienda/carrito.php');
exit();
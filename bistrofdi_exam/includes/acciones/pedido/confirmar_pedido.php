<?php
declare(strict_types=1);

/**
 * Convierte el carrito en un pedido real en la BD - Versión con soporte de Ofertas.
 */

require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../negocio/PedidoServiceApp.php';
require_once __DIR__ . '/../../negocio/PedidoDTO.php';
require_once __DIR__ . '/../../integracion/OfertasDAO.php'; // <-- NUEVO: Para guardar la relación pedido-oferta

exigirLogin();
exigirRol('cliente');

$redirectCarrito = BASE_URL . '/includes/vistas/pedido/carrito.php';
$redirectCatalogo = BASE_URL . '/includes/vistas/tienda/catalogo.php';
$redirectInicioPedido = BASE_URL . '/includes/vistas/pedido/pedido_inicio.php';
$redirectLogin = BASE_URL . '/includes/vistas/auth/login.php';
$redirectMisPedidos = BASE_URL . '/includes/vistas/pedido/mis_pedidos.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $redirectCarrito);
    exit();
}

$idUsuario = filter_var($_SESSION['id_usuario'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

$tipoPedido = $_SESSION['tipoPedido'] ?? null;
$carrito = $_SESSION['carrito'] ?? null;
$ofertasAplicadas = $_SESSION['ofertas_aplicadas'] ?? []; // <-- NUEVO: Recuperar ofertas de la sesión

$metodoPago = trim((string) (filter_input(INPUT_POST, 'metodo_pago', FILTER_UNSAFE_RAW) ?? ''));

$metodosPermitidos = ['camarero', 'tarjeta'];
$tiposPedidoPermitidos = ['Local', 'Llevar'];

if ($idUsuario === false || $idUsuario === null) {
    $_SESSION['mensaje_error'] = 'Sesión no válida.';
    header('Location: ' . $redirectLogin);
    exit();
}

if (!is_array($carrito) || empty($carrito) || !carritoValido($carrito)) {
    $_SESSION['mensaje_error'] = 'El carrito está vacío o no es válido.';
    header('Location: ' . $redirectCatalogo);
    exit();
}

if (!is_string($tipoPedido) || !in_array($tipoPedido, $tiposPedidoPermitidos, true)) {
    $_SESSION['mensaje_error'] = 'Debes elegir un tipo de pedido válido.';
    header('Location: ' . $redirectInicioPedido);
    exit();
}

if (!in_array($metodoPago, $metodosPermitidos, true)) {
    $_SESSION['mensaje_error'] = 'Debes seleccionar un método de pago válido.';
    header('Location: ' . $redirectCarrito);
    exit();
}

// <-- NUEVO: CÁLCULO DEL DESCUENTO TOTAL ACUMULADO -->
$descuentoAcumulado = 0.0;
foreach ($ofertasAplicadas as $of) {
    $descuentoAcumulado += (float) ($of['descuento'] ?? 0.0);
}

$db = Application::getInstance()->conexionBd();
$pedidoService = new PedidoServiceApp($db);
$ofertaDAO = new OfertaDAO($db);

$pedidoDTO = new PedidoDTO();
$pedidoDTO->setClienteId((int) $idUsuario);
$pedidoDTO->setTipo($tipoPedido);
$pedidoDTO->setProductos(normalizarCarrito($carrito));
$pedidoDTO->setDescuentoTotal($descuentoAcumulado); // <-- NUEVO: Inyectamos el descuento al DTO

$idPedido = $pedidoService->crearPedido($pedidoDTO);

if (!$idPedido) {
    $_SESSION['mensaje_error'] = 'Hubo un problema al conectar con la cocina. Por favor, vuelve a intentarlo.';
    header('Location: ' . $redirectCarrito);
    exit();
}

// <-- NUEVO: PERSISTIR LA RELACIÓN EN LA TABLA pedidos_ofertas -->
if (!empty($ofertasAplicadas)) {
    foreach ($ofertasAplicadas as $idOferta => $datos) {
        $ofertaDAO->vincularPedidoOferta(
            (int) $idPedido,
            (int) $idOferta,
            (int) $datos['veces_aplicada'],
            (float) $datos['descuento']
        );
    }
}

// Limpiamos todo de la sesión una vez el pedido está en BD
unset($_SESSION['carrito'], $_SESSION['tipoPedido'], $_SESSION['ofertas_aplicadas']); 

if ($metodoPago === 'camarero') {
    $_SESSION['mensaje_exito'] = '¡Pedido registrado! Avisa al camarero o en mostrador para pagar.';
    header('Location: ' . $redirectMisPedidos);
    exit();
}

header('Location: ' . BASE_URL . '/includes/vistas/pedido/pago.php?id=' . urlencode((string) $idPedido));
exit();
/**
 * Comprueba que el carrito tenga ids y cantidades válidas.
 */
function carritoValido(array $carrito): bool
{
    foreach ($carrito as $productoId => $cantidad) {
        if (filter_var($productoId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
            return false;
        }

        if (filter_var($cantidad, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
            return false;
        }
    }

    return true;
}

/**
 * Devuelve el carrito con claves y valores enteros.
 */
function normalizarCarrito(array $carrito): array
{
    $carritoNormalizado = [];

    foreach ($carrito as $productoId => $cantidad) {
        $carritoNormalizado[(int) $productoId] = (int) $cantidad;
    }

    return $carritoNormalizado;
}
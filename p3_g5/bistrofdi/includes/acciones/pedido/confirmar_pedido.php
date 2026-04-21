<?php
declare(strict_types=1);

/**
 * Convierte el carrito en un pedido real en la BD.
 */

require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../negocio/PedidoServiceApp.php';
require_once __DIR__ . '/../../negocio/PedidoDTO.php';

exigirLogin();
exigirRol('cliente');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../vistas/tienda/carrito.php');
    exit();
}

$idUsuario = filter_var($_SESSION['id_usuario'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

$tipoPedido = $_SESSION['tipoPedido'] ?? null;
$carrito = $_SESSION['carrito'] ?? null;

$metodoPago = trim((string) (filter_input(INPUT_POST, 'metodo_pago', FILTER_UNSAFE_RAW) ?? ''));
$metodosPermitidos = ['camarero', 'tarjeta'];
$tiposPedidoPermitidos = ['Local', 'Llevar'];

if ($idUsuario === false || $idUsuario === null) {
    $_SESSION['mensaje_error'] = 'Sesión no válida.';
    header('Location: ../../vistas/auth/login.php');
    exit();
}

if (!is_array($carrito) || empty($carrito) || !carritoValido($carrito)) {
    $_SESSION['mensaje_error'] = 'El carrito está vacío o no es válido.';
    header('Location: ../../vistas/tienda/catalogo.php');
    exit();
}

if (!is_string($tipoPedido) || !in_array($tipoPedido, $tiposPedidoPermitidos, true)) {
    $_SESSION['mensaje_error'] = 'Debes elegir un tipo de pedido válido.';
    header('Location: ../../vistas/pedido/pedido_inicio.php');
    exit();
}

if (!in_array($metodoPago, $metodosPermitidos, true)) {
    $_SESSION['mensaje_error'] = 'Debes seleccionar un método de pago válido.';
    header('Location: ../../vistas/tienda/carrito.php');
    exit();
}

$pedidoService = new PedidoServiceApp();

$pedidoDTO = new PedidoDTO();
$pedidoDTO->setClienteId((int) $idUsuario);
$pedidoDTO->setTipo($tipoPedido);
$pedidoDTO->setProductos(normalizarCarrito($carrito));

$idPedido = $pedidoService->crearPedido($pedidoDTO);

if (!$idPedido) {
    $_SESSION['mensaje_error'] = 'Hubo un problema al conectar con la cocina. Por favor, vuelve a intentarlo.';
    header('Location: ../../vistas/tienda/carrito.php');
    exit();
}

unset($_SESSION['carrito'], $_SESSION['tipoPedido']);

if ($metodoPago === 'camarero') {
    $_SESSION['mensaje_exito'] = '¡Pedido registrado! Avisa al camarero o en mostrador para pagar.';
    header('Location: ../../vistas/pedido/mis_pedidos.php');
    exit();
}

header('Location: ../../vistas/pedido/pago.php?id=' . urlencode((string) $idPedido));
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
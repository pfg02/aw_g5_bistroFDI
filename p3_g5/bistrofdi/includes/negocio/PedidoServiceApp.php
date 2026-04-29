<?php
declare(strict_types=1);

require_once __DIR__ . '/../integracion/PedidoDAO.php';
require_once __DIR__ . '/../integracion/ProductoDAO.php';
require_once __DIR__ . '/../core/config.php';

class PedidoServiceApp
{
    private PedidoDAO $pedidoDAO;
    private ProductoDAO $productoDAO;

    public function __construct(?mysqli $db = null)
    {
        $db = $db ?? Application::getInstance()->conexionBd();

        $this->pedidoDAO = new PedidoDAO($db);
        $this->productoDAO = new ProductoDAO($db);
    }
/**
     * Crea un pedido calculando correctamente el precio con IVA y aplicando los descuentos del DTO.
     */
    public function crearPedido($pedidoDTO): int
    {
        $productos = $pedidoDTO->getProductos();
        $totalSinDescuento = 0.0;

        // Calculamos el coste total real (con IVA) de los productos
        foreach ($productos as $idProducto => $cantidad) {
            $producto = $this->productoDAO->obtenerPorId((int) $idProducto);

            if ($producto) {
                $precioBase = $this->obtenerPrecioBaseProducto($producto);
                $porcentajeIva = $this->obtenerIvaProducto($producto);

                $precioConIva = $precioBase * (1 + ($porcentajeIva / 100));
                $totalSinDescuento += $precioConIva * (int) $cantidad;
            }
        }

        // Extraemos el descuento
        $descuentoAplicado = (float) $pedidoDTO->getDescuentoTotal();

        // Calculamos el precio final (nunca menor a 0)
        $totalFinal = max(0.0, $totalSinDescuento - $descuentoAplicado);

        // Asignamos la información contable al DTO antes de enviarlo al DAO
        $pedidoDTO->setTotalSinDescuento($totalSinDescuento);
        $pedidoDTO->setDescuentoTotal($descuentoAplicado);
        $pedidoDTO->setTotal($totalFinal);
        
        $pedidoDTO->setEstado('Recibido');
        $pedidoDTO->setFecha(date('Y-m-d H:i:s'));

        return $this->pedidoDAO->guardarPedido($pedidoDTO);
    }

	/**
     * MOTOR DE OFERTAS: Comprueba si una oferta es aplicable al carrito y calcula el descuento.
     */
	public function calcularDescuentoOferta(array $carrito, array $ofertasYaAplicadas, OfertaDTO $nuevaOferta): array
    {
        // Construimos un pool con el carrito para no aplicar ofertas a los mismos productos
        $pool = $carrito; 

        // Restamos los productos "consumidos" por ofertas anteriores
        foreach ($ofertasYaAplicadas as $ofertaAplicada) {
            $vecesAplicada = (int)$ofertaAplicada['veces_aplicada'];
            foreach ($ofertaAplicada['productos_requeridos'] as $req) {
                $idProd = (int)$req['producto_id'];
                $cantConsumida = (int)$req['cantidad'] * $vecesAplicada;
                
                if (isset($pool[$idProd])) {
                    $pool[$idProd] -= $cantConsumida;
                }
            }
        }

        // Evaluamos la nueva oferta
        $productosRequeridos = $nuevaOferta->getProductos();
        if (empty($productosRequeridos)) {
            return ['exito' => false, 'mensaje' => 'La oferta no tiene productos configurados.'];
        }

        $vecesAplicable = PHP_INT_MAX;
        $precioOriginalPack = 0.00;

        foreach ($productosRequeridos as $req) {
            $idReq = (int) $req['producto_id'];
            $cantReq = (int) $req['cantidad'];

            // Si el pool restante no tiene suficiente cantidad, la oferta no es aplicable (0 veces)
            if (!isset($pool[$idReq]) || $pool[$idReq] < $cantReq) {
                $vecesAplicable = 0;
                break;
            }

            // Calculamos automáticamente cuántas veces cabe (Múltiples veces de forma automática)
            $vecesPorProducto = (int) floor($pool[$idReq] / $cantReq);
            $vecesAplicable = min($vecesAplicable, $vecesPorProducto);

            // Sumamos el valor para calcular el descuento
            $productoObj = $this->productoDAO->obtenerPorId($idReq);
            if ($productoObj) {
                $precioBase = $this->obtenerPrecioBaseProducto($productoObj);
                $iva = $this->obtenerIvaProducto($productoObj);
                $precioConIva = $precioBase * (1 + ($iva / 100));
                
                $precioOriginalPack += ($precioConIva * $cantReq);
            }
        }

        // Feedback para el usuario
        if ($vecesAplicable > 0) {
            $porcentaje = (float) $nuevaOferta->getDescuentoPorcentaje();
            $descuentoTotal = ($precioOriginalPack * ($porcentaje / 100)) * $vecesAplicable;

            return [
                'exito' => true,
                'veces_aplicada' => $vecesAplicable,
                'descuento' => round($descuentoTotal, 2),
                'mensaje' => "¡Oferta aplicada con éxito! (x{$vecesAplicable})",
                'productos_requeridos' => $productosRequeridos 
            ];
        }

        return [
            'exito' => false,
            'mensaje' => 'No te quedan productos suficientes en el pedido sin descuento para aplicar esta oferta.'
        ];
    }

    public function obtenerPedido($idPedido)
    {
        return $this->pedidoDAO->buscarPedido((int) $idPedido);
    }

    public function actualizarEstado($idPedido, $nuevoEstado): bool
    {
        return $this->pedidoDAO->actualizarEstado((int) $idPedido, (string) $nuevoEstado);
    }

    public function asignarCocinero($idPedido, $idCocinero, $nuevoEstado = 'Cocinando'): bool
    {
        $idCocineroFinal = $idCocinero !== null ? (int) $idCocinero : null;

        return $this->pedidoDAO->asignarCocinero(
            (int) $idPedido,
            $idCocineroFinal,
            (string) $nuevoEstado
        );
    }

    public function obtenerPedidoActivoDeCocinero($idCocinero)
    {
        return $this->pedidoDAO->obtenerPedidoActivoDeCocinero((int) $idCocinero);
    }

    public function obtenerPedidosPorCliente($idCliente): array
    {
        return $this->pedidoDAO->obtenerPedidosPorCliente((int) $idCliente);
    }

    public function obtenerPedidosActivos(): array
    {
        return $this->pedidoDAO->obtenerPedidosActivos();
    }

    public function obtenerProductosDePedido($idPedido): array
    {
        return $this->pedidoDAO->obtenerProductosDePedido((int) $idPedido);
    }

    public function verPedidosPorEstado($estado): array
    {
        return $this->pedidoDAO->verPedidosPorEstado((string) $estado);
    }

    public function eliminarPedido($idPedido): bool
    {
        return $this->pedidoDAO->eliminarPedido((int) $idPedido);
    }

    public function marcarProductoComoPreparado($idPedido, $idProducto): bool
    {
        return $this->pedidoDAO->marcarProductoComoPreparado(
            (int) $idPedido,
            (int) $idProducto
        );
    }

    public function todosProductosCocinaPreparados($idPedido): bool
    {
        return $this->pedidoDAO->todosProductosCocinaPreparados((int) $idPedido);
    }

    public function marcarProductoServidoSala($idPedido, $idProducto): bool
    {
        return $this->pedidoDAO->marcarProductoServidoSala(
            (int) $idPedido,
            (int) $idProducto
        );
    }

    public function marcarPedidoServidoSala($idPedido): bool
    {
        return $this->pedidoDAO->marcarPedidoServidoSala((int) $idPedido);
    }

    public function pedidoPuedeCancelarse($pedido): bool
    {
        if (!$pedido) {
            return false;
        }

        $estado = method_exists($pedido, 'getEstado')
            ? (string) $pedido->getEstado()
            : '';

        $servidoSala = 0;

        if (method_exists($pedido, 'getServidoSala')) {
            $servidoSala = (int) $pedido->getServidoSala();
        }

        if ($servidoSala === 1) {
            return false;
        }

        return in_array($estado, ['Nuevo', 'Recibido', 'En preparación'], true);
    }

    public function cancelarPedido($idPedido): bool
    {
        $pedido = $this->obtenerPedido((int) $idPedido);

        if (!$this->pedidoPuedeCancelarse($pedido)) {
            return false;
        }

        return $this->pedidoDAO->actualizarEstado((int) $idPedido, 'Cancelado');
    }

    private function obtenerPrecioBaseProducto($producto): float
    {
        if (is_array($producto)) {
            if (isset($producto['precio_base'])) {
                return (float) $producto['precio_base'];
            }

            if (isset($producto['precio'])) {
                return (float) $producto['precio'];
            }
        }

        if (is_object($producto)) {
            if (method_exists($producto, 'getPrecioBase')) {
                return (float) $producto->getPrecioBase();
            }

            if (method_exists($producto, 'getPrecio')) {
                return (float) $producto->getPrecio();
            }

            if (isset($producto->precio_base)) {
                return (float) $producto->precio_base;
            }

            if (isset($producto->precio)) {
                return (float) $producto->precio;
            }
        }

        return 0.0;
    }

    private function obtenerIvaProducto($producto): float
    {
        if (is_array($producto) && isset($producto['iva'])) {
            return (float) $producto['iva'];
        }

        if (is_object($producto)) {
            if (method_exists($producto, 'getIva')) {
                return (float) $producto->getIva();
            }

            if (isset($producto->iva)) {
                return (float) $producto->iva;
            }
        }

        return 0.0;
    }
}
?>
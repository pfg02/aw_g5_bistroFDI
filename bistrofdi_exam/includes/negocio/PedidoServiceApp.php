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

        foreach ($productos as $idProducto => $cantidad) {
            $producto = $this->productoDAO->obtenerPorId((int) $idProducto);

            if ($producto) {
                $precioBase = $this->obtenerPrecioBaseProducto($producto);
                $porcentajeIva = $this->obtenerIvaProducto($producto);

                $precioConIva = $precioBase * (1 + ($porcentajeIva / 100));
                $totalSinDescuento += $precioConIva * (int) $cantidad;
            }
        }

        $descuentoAplicado = (float) $pedidoDTO->getDescuentoTotal();
        $totalFinal = max(0.0, $totalSinDescuento - $descuentoAplicado);

        $pedidoDTO->setTotalSinDescuento($totalSinDescuento);
        $pedidoDTO->setDescuentoTotal($descuentoAplicado);
        $pedidoDTO->setTotal($totalFinal);

        /*
         * Al crear el pedido, todavía no está pagado.
         * Por eso empieza en Recibido.
         */
        $pedidoDTO->setEstado('Recibido');
        $pedidoDTO->setFecha(date('Y-m-d H:i:s'));

        return $this->pedidoDAO->guardarPedido($pedidoDTO);
    }

    /**
     * MOTOR DE OFERTAS: Comprueba si una oferta es aplicable al carrito y calcula el descuento.
     */
    public function calcularDescuentoOferta(array $carrito, array $ofertasYaAplicadas, OfertaDTO $nuevaOferta): array
    {
        $pool = $carrito;

        foreach ($ofertasYaAplicadas as $ofertaAplicada) {
            $vecesAplicada = (int) $ofertaAplicada['veces_aplicada'];

            foreach ($ofertaAplicada['productos_requeridos'] as $req) {
                $idProd = (int) $req['producto_id'];
                $cantConsumida = (int) $req['cantidad'] * $vecesAplicada;

                if (isset($pool[$idProd])) {
                    $pool[$idProd] -= $cantConsumida;
                }
            }
        }

        $productosRequeridos = $nuevaOferta->getProductos();

        if (empty($productosRequeridos)) {
            return [
                'exito' => false,
                'mensaje' => 'La oferta no tiene productos configurados.'
            ];
        }

        $vecesAplicable = PHP_INT_MAX;
        $precioOriginalPack = 0.00;

        foreach ($productosRequeridos as $req) {
            $idReq = (int) $req['producto_id'];
            $cantReq = (int) $req['cantidad'];

            if (!isset($pool[$idReq]) || $pool[$idReq] < $cantReq) {
                $vecesAplicable = 0;
                break;
            }

            $vecesPorProducto = (int) floor($pool[$idReq] / $cantReq);
            $vecesAplicable = min($vecesAplicable, $vecesPorProducto);

            $productoObj = $this->productoDAO->obtenerPorId($idReq);

            if ($productoObj) {
                $precioBase = $this->obtenerPrecioBaseProducto($productoObj);
                $iva = $this->obtenerIvaProducto($productoObj);
                $precioConIva = $precioBase * (1 + ($iva / 100));

                $precioOriginalPack += ($precioConIva * $cantReq);
            }
        }

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
            'mensaje' => 'Descuento no aplicable a los productos actuales del carrito'
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

    /*
     * Regla de cancelación:
     * - Nuevo: se puede cancelar.
     * - Recibido: se puede cancelar.
     * - En preparación, Cocinando, Listo cocina, Terminado, Entregado: no se puede cancelar.
     */
    public function pedidoPuedeCancelarse($pedido): bool
    {
        if (!$pedido) {
            return false;
        }

        $estado = '';

        if (is_array($pedido)) {
            $estado = trim((string) ($pedido['estado'] ?? ''));
        } elseif (is_object($pedido) && method_exists($pedido, 'getEstado')) {
            $estado = trim((string) $pedido->getEstado());
        }

        return in_array($estado, ['Nuevo', 'Recibido'], true);
    }

    public function cancelarPedido($idPedido): bool
    {
        $pedido = $this->obtenerPedido((int) $idPedido);

        if (!$this->pedidoPuedeCancelarse($pedido)) {
            return false;
        }

        return $this->pedidoDAO->actualizarEstado((int) $idPedido, 'Cancelado');
    }

    /*
     * Regla después del pago:
     * - Si solo hay bebidas: pasa a Listo cocina.
     * - Si hay comida o mezcla comida/bebida: pasa a En preparación.
     */
    public function obtenerEstadoTrasPago($idPedido): string
    {
        return $this->pedidoSoloTieneBebidas((int) $idPedido)
            ? 'Listo cocina'
            : 'En preparación';
    }

    public function pedidoSoloTieneBebidas($idPedido): bool
    {
        $productos = $this->obtenerProductosDePedido((int) $idPedido);

        if (empty($productos)) {
            return false;
        }

        foreach ($productos as $producto) {
            $categoria = $this->obtenerCategoriaProductoPedido($producto);
            $categoriaNormalizada = $this->normalizarTextoCategoria($categoria);

            if ($categoriaNormalizada !== 'bebida' && $categoriaNormalizada !== 'bebidas') {
                return false;
            }
        }

        return true;
    }

    private function obtenerCategoriaProductoPedido($producto): string
    {
        if (is_array($producto)) {
            return trim((string) (
                $producto['categoria']
                ?? $producto['nombre_categoria']
                ?? $producto['categoria_nombre']
                ?? $producto['tipo_categoria']
                ?? $producto['nombreCategoria']
                ?? ''
            ));
        }

        if (is_object($producto)) {
            if (method_exists($producto, 'getCategoria')) {
                return trim((string) $producto->getCategoria());
            }

            if (method_exists($producto, 'getNombreCategoria')) {
                return trim((string) $producto->getNombreCategoria());
            }

            if (method_exists($producto, 'getCategoriaNombre')) {
                return trim((string) $producto->getCategoriaNombre());
            }

            if (isset($producto->categoria)) {
                return trim((string) $producto->categoria);
            }

            if (isset($producto->nombre_categoria)) {
                return trim((string) $producto->nombre_categoria);
            }
        }

        return '';
    }

    private function normalizarTextoCategoria(string $texto): string
    {
        $texto = strtolower(trim($texto));

        $buscar = ['á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ'];
        $reemplazar = ['a', 'e', 'i', 'o', 'u', 'u', 'n'];

        return str_replace($buscar, $reemplazar, $texto);
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
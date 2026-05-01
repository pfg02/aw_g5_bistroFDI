<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../integracion/OfertasDAO.php';
require_once __DIR__ . '/../integracion/ProductoDAO.php';

class OfertasServiceApp
{
    private OfertaDAO $ofertasDAO;
    private ProductoDAO $productoDAO;

    public function __construct(?mysqli $db = null)
    {
        $db = $db ?? Application::getInstance()->conexionBd();

        $this->ofertasDAO = new OfertaDAO($db);
        $this->productoDAO = new ProductoDAO($db);
    }

    public function listarOfertas(): array
    {
        return $this->ofertasDAO->listarOfertas();
    }

    public function crearOferta($ofertaDTO): bool
    {
        return $this->ofertasDAO->crearOferta($ofertaDTO);
    }

    public function actualizarOferta($ofertaDTO): bool
    {
        return $this->ofertasDAO->actualizarOferta($ofertaDTO);
    }

    public function borrarOferta(int $id): bool
    {
        return $this->ofertasDAO->borrarOferta($id);
    }

    public function eliminarOferta(int $id): bool
    {
        return $this->ofertasDAO->borrarOferta($id);
    }

    public function eliminar(int $id): bool
    {
        return $this->ofertasDAO->borrarOferta($id);
    }

    public function esOfertaActiva($ofertaDTO): bool
    {
        return $this->ofertasDAO->esOfertaActiva($ofertaDTO);
    }

    public function vincularPedidoOferta(int $idPedido, int $idOferta, int $veces, float $descuento): bool
    {
        return $this->ofertasDAO->vincularPedidoOferta($idPedido, $idOferta, $veces, $descuento);
    }

    public function obtenerOfertasDePedido(int $idPedido): array
    {
        return $this->ofertasDAO->obtenerOfertasDePedido($idPedido);
    }

    public function obtenerOfertasActivas(): array
    {
        return $this->ofertasDAO->obtenerOfertasActivas();
    }

    public function obtenerPorId(int $idOferta)
    {
        if (method_exists($this->ofertasDAO, 'obtenerPorId')) {
            return $this->ofertasDAO->obtenerPorId($idOferta);
        }

        if (method_exists($this->ofertasDAO, 'buscarPorId')) {
            return $this->ofertasDAO->buscarPorId($idOferta);
        }

        if (method_exists($this->ofertasDAO, 'obtenerOfertaPorId')) {
            return $this->ofertasDAO->obtenerOfertaPorId($idOferta);
        }

        return null;
    }

    /*
     * Método central para preparar una oferta antes de guardarla.
     */
    public function prepararOfertaParaGuardar($ofertaDTO, array $productosNormalizados, float $precioFinalManual): array
    {
        $errores = [];

        if (empty($productosNormalizados)) {
            $errores[] = 'Debes añadir al menos un producto a la oferta.';
        }

        $precioPack = $this->calcularPrecioPackConIva($productosNormalizados);

        if ($precioPack <= 0) {
            $errores[] = 'No se ha podido calcular el precio del pack.';
        }

        if ($precioFinalManual <= 0) {
            $errores[] = 'El precio final de la oferta debe ser mayor que 0.';
        }

        if ($precioPack > 0 && $precioFinalManual > $precioPack) {
            $errores[] = 'El precio final de la oferta no puede ser mayor que el precio del pack.';
        }

        if (!empty($errores)) {
            return [
                'exito' => false,
                'errores' => $errores,
                'oferta' => $ofertaDTO,
                'precio_pack' => $precioPack,
                'precio_final' => $precioFinalManual,
                'descuento_porcentaje' => 0.0,
            ];
        }

        $descuentoPorcentaje = $this->calcularDescuentoDesdePrecioFinal(
            $precioPack,
            $precioFinalManual
        );

        if (method_exists($ofertaDTO, 'setProductos')) {
            $ofertaDTO->setProductos($productosNormalizados);
        }

        if (method_exists($ofertaDTO, 'setDescuentoPorcentaje')) {
            $ofertaDTO->setDescuentoPorcentaje($descuentoPorcentaje);
        }

        if (method_exists($ofertaDTO, 'setPrecioFinal')) {
            $ofertaDTO->setPrecioFinal(round($precioFinalManual, 2));
        }

        return [
            'exito' => true,
            'errores' => [],
            'oferta' => $ofertaDTO,
            'precio_pack' => round($precioPack, 2),
            'precio_final' => round($precioFinalManual, 2),
            'descuento_porcentaje' => $descuentoPorcentaje,
        ];
    }

    public function calcularPrecioFinalDesdeDescuento(float $precioPack, float $descuentoPorcentaje): float
    {
        if ($precioPack <= 0) {
            return 0.0;
        }

        if ($descuentoPorcentaje < 0) {
            $descuentoPorcentaje = 0;
        }

        if ($descuentoPorcentaje > 100) {
            $descuentoPorcentaje = 100;
        }

        return round($precioPack - ($precioPack * ($descuentoPorcentaje / 100)), 2);
    }

    public function calcularDescuentoDesdePrecioFinal(float $precioPack, float $precioFinal): float
    {
        if ($precioPack <= 0) {
            return 0.0;
        }

        $descuento = (($precioPack - $precioFinal) / $precioPack) * 100;

        if ($descuento < 0) {
            $descuento = 0;
        }

        if ($descuento > 100) {
            $descuento = 100;
        }

        return round($descuento, 2);
    }

    public function calcularPrecioPackConIva(array $productosOferta): float
    {
        $total = 0.0;

        foreach ($productosOferta as $productoOferta) {
            $cantidad = $this->obtenerCantidadProductoOferta($productoOferta);

            if ($cantidad <= 0) {
                continue;
            }

            $precioBase = $this->obtenerPrecioBaseDesdeProductoOferta($productoOferta);
            $iva = $this->obtenerIvaDesdeProductoOferta($productoOferta);

            if ($precioBase === null) {
                $idProducto = $this->obtenerIdProductoOferta($productoOferta);

                if ($idProducto === null) {
                    continue;
                }

                $producto = $this->productoDAO->obtenerPorId($idProducto);

                if (!$producto) {
                    continue;
                }

                $precioBase = $this->obtenerPrecioBaseProducto($producto);
                $iva = $this->obtenerIvaProducto($producto);
            }

            $precioConIva = $precioBase * (1 + ($iva / 100));
            $total += $precioConIva * $cantidad;
        }

        return round($total, 2);
    }

    public function revalidarOfertasTrasEliminacion(array $carritoActual, array $ofertasEnSesion): array
    {
        $ofertasValidas = [];
        $carritoCopia = $carritoActual;

        foreach ($ofertasEnSesion as $idOferta => $datosSesion) {
            $ofertaDTO = $this->obtenerPorId((int) $idOferta);

            if (!$ofertaDTO || !method_exists($ofertaDTO, 'getProductos')) {
                continue;
            }

            $mapaRequerimientos = [];

            foreach ($ofertaDTO->getProductos() as $productoOferta) {
                $productoId = (int) ($productoOferta['producto_id'] ?? 0);
                $cantidad = (int) ($productoOferta['cantidad'] ?? 0);

                if ($productoId > 0 && $cantidad > 0) {
                    $mapaRequerimientos[$productoId] = $cantidad;
                }
            }

            if (empty($mapaRequerimientos)) {
                continue;
            }

            $vecesPosibles = $this->calcularVecesAplicable($carritoCopia, $mapaRequerimientos);

            if ($vecesPosibles <= 0) {
                continue;
            }

            $vecesAplicadaSesion = (int) ($datosSesion['veces_aplicada'] ?? 1);
            $vecesAFijar = min($vecesPosibles, $vecesAplicadaSesion);

            $precioOriginalPack = $this->calcularPrecioPackConIva($ofertaDTO->getProductos());
            $descuentoPorcentaje = method_exists($ofertaDTO, 'getDescuentoPorcentaje')
                ? (float) $ofertaDTO->getDescuentoPorcentaje()
                : 0.0;

            $nuevoDescuento = ($precioOriginalPack * ($descuentoPorcentaje / 100)) * $vecesAFijar;

            $ofertasValidas[$idOferta] = [
                'nombre' => method_exists($ofertaDTO, 'getNombre') ? $ofertaDTO->getNombre() : 'Oferta',
                'veces_aplicada' => $vecesAFijar,
                'descuento' => round($nuevoDescuento, 2),
                'productos_requeridos' => $ofertaDTO->getProductos(),
            ];

            $this->consumirProductos($carritoCopia, $mapaRequerimientos, $vecesAFijar);
        }

        return $ofertasValidas;
    }

    private function calcularVecesAplicable(array $carrito, array $productosOferta): int
    {
        $veces = PHP_INT_MAX;

        foreach ($productosOferta as $productoId => $cantidadNecesaria) {
            $productoId = (int) $productoId;
            $cantidadNecesaria = (int) $cantidadNecesaria;

            if ($productoId <= 0 || $cantidadNecesaria <= 0) {
                return 0;
            }

            if (!isset($carrito[$productoId])) {
                return 0;
            }

            $cantidadEnCarrito = (int) $carrito[$productoId];

            if ($cantidadEnCarrito < $cantidadNecesaria) {
                return 0;
            }

            $vecesProducto = intdiv($cantidadEnCarrito, $cantidadNecesaria);
            $veces = min($veces, $vecesProducto);
        }

        return $veces === PHP_INT_MAX ? 0 : $veces;
    }

    private function consumirProductos(array &$carrito, array $productosOferta, int $veces): void
    {
        foreach ($productosOferta as $productoId => $cantidad) {
            $productoId = (int) $productoId;
            $cantidad = (int) $cantidad;

            if (!isset($carrito[$productoId])) {
                continue;
            }

            $carrito[$productoId] -= $cantidad * $veces;

            if ($carrito[$productoId] <= 0) {
                unset($carrito[$productoId]);
            }
        }
    }

    private function obtenerIdProductoOferta($productoOferta): ?int
    {
        if (is_array($productoOferta)) {
            $id = $productoOferta['producto_id']
                ?? $productoOferta['id_producto']
                ?? $productoOferta['id']
                ?? null;

            $id = filter_var($id, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1]
            ]);

            return $id === false ? null : (int) $id;
        }

        if (is_object($productoOferta)) {
            if (method_exists($productoOferta, 'getProductoId')) {
                $id = $productoOferta->getProductoId();
                $id = filter_var($id, FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => 1]
                ]);

                return $id === false ? null : (int) $id;
            }

            if (method_exists($productoOferta, 'getId')) {
                $id = $productoOferta->getId();
                $id = filter_var($id, FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => 1]
                ]);

                return $id === false ? null : (int) $id;
            }
        }

        return null;
    }

    private function obtenerCantidadProductoOferta($productoOferta): int
    {
        if (is_array($productoOferta)) {
            return (int) ($productoOferta['cantidad'] ?? 1);
        }

        if (is_object($productoOferta) && method_exists($productoOferta, 'getCantidad')) {
            return (int) $productoOferta->getCantidad();
        }

        return 1;
    }

    private function obtenerPrecioBaseDesdeProductoOferta($productoOferta): ?float
    {
        if (is_array($productoOferta)) {
            if (isset($productoOferta['precio_base'])) {
                return (float) $productoOferta['precio_base'];
            }

            if (isset($productoOferta['precio'])) {
                return (float) $productoOferta['precio'];
            }
        }

        if (is_object($productoOferta)) {
            if (method_exists($productoOferta, 'getPrecioBase')) {
                return (float) $productoOferta->getPrecioBase();
            }

            if (method_exists($productoOferta, 'getPrecio')) {
                return (float) $productoOferta->getPrecio();
            }
        }

        return null;
    }

    private function obtenerIvaDesdeProductoOferta($productoOferta): float
    {
        if (is_array($productoOferta) && isset($productoOferta['iva'])) {
            return (float) $productoOferta['iva'];
        }

        if (is_object($productoOferta) && method_exists($productoOferta, 'getIva')) {
            return (float) $productoOferta->getIva();
        }

        return 0.0;
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
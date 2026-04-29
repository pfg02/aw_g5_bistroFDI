<?php
declare(strict_types=1);

require_once __DIR__ . "/OfertasServiceApp.php";
require_once __DIR__ . "/OfertasDTO.php";

class OfertasController
{
    private static ?OfertasController $instancia = null;
    private OfertasServiceApp $ofertaService;

    private function __construct(?mysqli $db = null)
    {
        $this->ofertaService = new OfertasServiceApp($db);
    }

    public static function getInstance(?mysqli $db = null): OfertasController
    {
        if (self::$instancia === null) {
            self::$instancia = new OfertasController($db);
        }

        return self::$instancia;
    }

    public function obtenerOfertasActivas(): array
    {
        return $this->ofertaService->obtenerOfertasActivas();
    }

    public function obtenerOfertaPorId(int $id)
    {
        return $this->ofertaService->obtenerPorId($id);
    }

    /*
     * Calcula el descuento total de las ofertas seleccionadas para un carrito.
     *
     * $carrito = [productoId => cantidad]
     * $idsOfertas = [1, 2, 3]
     */
    public function procesarDescuentosCarrito(array $carrito, array $idsOfertas): float
    {
        return $this->ofertaService->calcularDescuento($carrito, $idsOfertas);
    }

    /*
     * Revalida las ofertas cuando el usuario elimina productos del carrito.
     */
    public function revalidarOfertasTrasEliminacion(array $carritoActual, array $ofertasEnSesion): array
    {
        return $this->ofertaService->revalidarOfertasTrasEliminacion(
            $carritoActual,
            $ofertasEnSesion
        );
    }

    /*
     * Prepara una oferta antes de guardarla:
     * - calcula precio pack
     * - valida precio final
     * - recalcula descuento real desde el precio final manual
     */
    public function prepararOfertaParaGuardar(OfertaDTO $oferta, array $productosNormalizados, float $precioFinalManual): array
    {
        return $this->ofertaService->prepararOfertaParaGuardar(
            $oferta,
            $productosNormalizados,
            $precioFinalManual
        );
    }

    public function calcularPrecioPackConIva(array $productosOferta): float
    {
        return $this->ofertaService->calcularPrecioPackConIva($productosOferta);
    }

    public function calcularPrecioFinalDesdeDescuento(float $precioPack, float $descuentoPorcentaje): float
    {
        return $this->ofertaService->calcularPrecioFinalDesdeDescuento(
            $precioPack,
            $descuentoPorcentaje
        );
    }

    public function calcularDescuentoDesdePrecioFinal(float $precioPack, float $precioFinal): float
    {
        return $this->ofertaService->calcularDescuentoDesdePrecioFinal(
            $precioPack,
            $precioFinal
        );
    }

    public function borrarOferta(int $id): bool
    {
        /*
         * Este método necesita que exista borrarOferta() en OfertasServiceApp.
         * Si todavía no lo tienes creado ahí, devuelve false para evitar error fatal.
         */
        if (!method_exists($this->ofertaService, 'borrarOferta')) {
            return false;
        }

        return $this->ofertaService->borrarOferta($id);
    }
}
?>
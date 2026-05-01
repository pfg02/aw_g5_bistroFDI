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


    public function listarOfertas(): array
    {
        return $this->ofertaService->listarOfertas();
    }

    public function crearOferta(OfertaDTO $ofertaDTO): bool
    {
        return $this->ofertaService->crearOferta($ofertaDTO);
    }

    public function actualizarOferta(OfertaDTO $ofertaDTO): bool
    {
        return $this->ofertaService->actualizarOferta($ofertaDTO);
    }

    public function esOfertaActiva(OfertaDTO $ofertaDTO): bool
    {
        return $this->ofertaService->esOfertaActiva($ofertaDTO);
    }

    public function vincularPedidoOferta(int $idPedido, int $idOferta, int $veces, float $descuento): bool
    {
        return $this->ofertaService->vincularPedidoOferta($idPedido, $idOferta, $veces, $descuento);
    }

    public function obtenerOfertasDePedido(int $idPedido): array
    {
        return $this->ofertaService->obtenerOfertasDePedido($idPedido);
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
        return $this->ofertaService->borrarOferta($id);
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
}
?>
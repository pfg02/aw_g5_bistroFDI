<?php
declare(strict_types=1);

require_once __DIR__ . "/OfertasServiceApp.php";
require_once __DIR__ . "/OfertasDTO.php";

class OfertasController {

    private static $instancia = null;
    private $ofertaService;

    private function __construct($db) {
        $this->ofertaService = new OfertasServiceApp($db);
    }

    public static function getInstance($db = null) {
        if (self::$instancia === null) {
            self::$instancia = new OfertasController($db);
        }
        return self::$instancia;
    }

    public function obtenerOfertasActivas() {
        return $this->ofertaService->obtenerOfertasActivas();
    }

    public function obtenerOfertaPorId(int $id) {
        return $this->ofertaService->obtenerPorId($id);
    }

    /**
     * Aplica la lógica de descuentos secuencialmente.
     * Devuelve un array con el descuento total y el desglose para la sesión/BD.
     */
    public function procesarDescuentosCarrito(array $carrito, array $idsOfertas) {
        return $this->ofertaService->calcularDescuentoTotal($carrito, $idsOfertas);
    }

    public function borrarOferta(int $id) {
        return $this->ofertaService->borrarOferta($id);
    }
}
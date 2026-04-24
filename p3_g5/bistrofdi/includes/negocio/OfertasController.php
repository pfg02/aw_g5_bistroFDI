<?php

require_once __DIR__ . "/OfertasServiceApp.php";
require_once __DIR__ . "/OfertasDTO.php";

class OfertasController {

    private static $instancia = null;

    private $ofertaService;

    /**
     * Constructor privado (singleton)
     */
    private function __construct($db) {
        $this->ofertaService = new OfertasServiceApp($db);
    }

    /**
     * Obtener instancia única
     */
    public static function getInstance($db) {

        if (self::$instancia === null) {
            self::$instancia = new OfertasController($db);
        }

        return self::$instancia;
    }

    /**
     * Obtener ofertas activas (para mostrar en la vista)
     */
    public function obtenerOfertasActivas() {
        return $this->ofertaService->obtenerOfertasActivas();
    }

    /**
     * Calcular descuento para un carrito
     * 
     * $carrito = [productoId => cantidad]
     * $ofertasSeleccionadas = [idOferta1, idOferta2...]
     */
    public function calcularDescuento($carrito, $ofertasSeleccionadas) {

        if (empty($carrito) || empty($ofertasSeleccionadas)) {
            return 0;
        }

        return $this->ofertaService->calcularDescuento(
            $carrito,
            $ofertasSeleccionadas
        );
    }

}
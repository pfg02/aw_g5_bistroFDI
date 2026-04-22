<?php

require_once __DIR__ . "/../integration/OfertasDAO.php";

class OfertasServiceApp {

    private $ofertasDAO;

    public function __construct($db) {
        $this->ofertasDAO = new OfertasDAO($db);
    }

    /**
     * Obtiene ofertas activas
     */
    public function obtenerOfertasActivas() {
        return $this->ofertasDAO->obtenerOfertasActivas();
    }

    /**
     * Calcula el descuento total aplicado a un carrito
     * 
     * $carrito = [productoId => cantidad]
     * $ofertasSeleccionadas = [idOferta1, idOferta2...]
     */
    public function calcularDescuento($carrito, $ofertasSeleccionadas) {

        $descuentoTotal = 0;

        // Copia para evitar reutilizar productos
        $carritoDisponible = $carrito;

        foreach ($ofertasSeleccionadas as $idOferta) {

            $productosOferta = $this->ofertasDAO->obtenerProductosDeOferta($idOferta);

            if (empty($productosOferta)) {
                continue;
            }

            // Convertir a formato [productoId => cantidad]
            $mapaOferta = [];
            foreach ($productosOferta as $p) {
                $mapaOferta[$p['producto_id']] = $p['cantidad'];
            }

            // Cuántas veces se puede aplicar
            $veces = $this->calcularVecesAplicable($carritoDisponible, $mapaOferta);

            if ($veces <= 0) {
                continue;
            }

            // Calcular precio del pack con IVA
            $precioPack = $this->calcularPrecioPack($productosOferta);

            // Obtener porcentaje desde DTO
            $ofertasActivas = $this->ofertasDAO->obtenerOfertasActivas();
            $ofertaDTO = $this->buscarOfertaPorId($ofertasActivas, $idOferta);

            if ($ofertaDTO === null) {
                continue;
            }

            $descuentoPorcentaje = $ofertaDTO->getDescuentoPorcentaje();

            $descuentoUnitario = $precioPack * ($descuentoPorcentaje / 100);

            $descuentoTotal += $descuentoUnitario * $veces;

            // Consumir productos
            $this->consumirProductos($carritoDisponible, $mapaOferta, $veces);
        }

        return $descuentoTotal;
    }

    /**
     * Calcula cuántas veces se puede aplicar una oferta
     */
    private function calcularVecesAplicable($carrito, $productosOferta) {

        $veces = PHP_INT_MAX;

        foreach ($productosOferta as $productoId => $cantidadNecesaria) {

            if (!isset($carrito[$productoId])) {
                return 0;
            }

            $vecesProducto = intdiv($carrito[$productoId], $cantidadNecesaria);

            $veces = min($veces, $vecesProducto);
        }

        return $veces;
    }

    /**
     * Calcula el precio del pack CON IVA
     */
    private function calcularPrecioPack($productosOferta) {

        $total = 0;

        foreach ($productosOferta as $p) {

            $precioBase = (float)$p['precio_base'];
            $iva = (float)$p['iva'];

            $precioConIVA = $precioBase * (1 + $iva / 100);

            $total += $precioConIVA * $p['cantidad'];
        }

        return $total;
    }

    /**
     * Evita reutilizar productos entre ofertas
     */
    private function consumirProductos(&$carrito, $productosOferta, $veces) {

        foreach ($productosOferta as $productoId => $cantidad) {

            $carrito[$productoId] -= $cantidad * $veces;

            if ($carrito[$productoId] <= 0) {
                unset($carrito[$productoId]);
            }
        }
    }

    /**
     * Busca una ofertaDTO por ID en lista
     */
    private function buscarOfertaPorId($ofertas, $id) {

        foreach ($ofertas as $oferta) {
            if ($oferta->getId() == $id) {
                return $oferta;
            }
        }

        return null;
    }
}
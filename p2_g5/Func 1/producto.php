<?php
class Producto {
    public $id;
    public $id_categoria;
    public $nombre;
    public $descripcion;
    public $precio_base;
    public $iva;
    public $stock;
    public $ofertado;

    public function __construct($id, $id_categoria, $nombre, $precio_base, $iva, $stock, $ofertado = 1) {
        $this->id = $id;
        $this->id_categoria = $id_categoria;
        $this->nombre = $nombre;
        $this->precio_base = $precio_base;
        $this->iva = $iva;
        $this->stock = $stock;
        $this->ofertado = $ofertado;
    }

    // Lógica requerida por el guion para el cálculo del precio final
    public function getPrecioFinal() {
        return $this->precio_base * (1 + ($this->iva / 100));
    }

    public function estaDisponible() {
        return $this->stock > 0 && $this->ofertado == 1;
    }
}
?>
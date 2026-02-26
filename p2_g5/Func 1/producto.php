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
    public $imagenes; // <-- Nueva propiedad para el array de imágenes

    public function __construct($id, $id_categoria, $nombre, $descripcion, $precio_base, $iva, $stock, $ofertado = 1, $imagenes = []) {
        $this->id = $id;
        $this->id_categoria = $id_categoria;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->precio_base = $precio_base;
        $this->iva = $iva;
        $this->stock = $stock;
        $this->ofertado = $ofertado;
        $this->imagenes = $imagenes; //
    }

    public function getPrecioFinal() {
        return $this->precio_base * (1 + ($this->iva / 100));
    }

    public function estaDisponible() {
        return $this->stock > 0 && $this->ofertado == 1;
    }

    // Método útil para la interfaz del cliente
    public function getImagenPrincipal() {
        return !empty($this->imagenes) ? $this->imagenes[0] : 'default_producto.png';
    }
}
?>
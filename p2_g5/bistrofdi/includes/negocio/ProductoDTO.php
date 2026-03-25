<?php
// includes/negocio/ProductoDTO.php

class ProductoDTO {
    public $id; 
    public $nombre; 
    public $descripcion; 
    public $precio; 
    public $stock; 
    public $imagen; 
    public $id_categoria; 
    public $ofertado;
    public $iva; 
    public $categoria_nombre;

    public function __construct($id=null, $nombre="", $descripcion="", $precio=0, $stock=0, $imagen=null, $id_categoria=null, $ofertado=1, $iva=21) {
        $this->id = $id; 
        $this->nombre = $nombre; 
        $this->descripcion = $descripcion;
        $this->precio = (float)$precio; 
        $this->stock = (int)$stock;
        $this->imagen = $imagen; 
        $this->id_categoria = $id_categoria;
        $this->ofertado = (int)$ofertado; 
        $this->iva = (int)$iva;
    }

    public function getPrecioFinal() {
        return $this->precio * (1 + ($this->iva / 100));
    }

    public function obtenerEstadoStock() {
        return ($this->stock > 0) ? "En stock" : "Sin stock";
    }
}
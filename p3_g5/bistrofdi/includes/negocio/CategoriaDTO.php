<?php
class CategoriaDTO {
    public $id;
    public $nombre;
    public $descripcion;
    public $imagen;

    public function __construct($id = null, $nombre = '', $descripcion = '', $imagen = 'default.png') {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->imagen = $imagen;
    }
}
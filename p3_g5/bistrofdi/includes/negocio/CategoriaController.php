<?php
require_once __DIR__ . '/CategoriaService.php';

class CategoriaController {
    private $service;

    public function __construct($db) {
        $this->service = new CategoriaService($db);
    }

    public function gestionarPeticion($accion, $id = null) {
        if ($accion === 'eliminar' && $id) {
            // Lógica de seguridad: ¿Tiene productos asociados?
            // Si todo ok, borrar.
        }
        // Redirigir a la vista de categorías
    }
}
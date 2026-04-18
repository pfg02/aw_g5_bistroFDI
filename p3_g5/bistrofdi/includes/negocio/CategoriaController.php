<?php
require_once __DIR__ . '/CategoriaService.php';

class CategoriaController
{
    private CategoriaService $service;

    public function __construct($db)
    {
        $this->service = new CategoriaService($db);
    }

    public function obtenerPorId(int $id)
    {
        return $this->service->obtenerPorId($id);
    }

    public function guardarCategoria(string $nombre, string $descripcion, ?string $imagen = null, ?int $id = null): bool
    {
        return $this->service->guardarCategoria($nombre, $descripcion, $imagen, $id);
    }

    public function gestionarPeticion($accion, $id = null)
    {
        if ($accion === 'eliminar' && $id) {
            return $this->service->eliminarCategoria($id);
        }

        return false;
    }
}
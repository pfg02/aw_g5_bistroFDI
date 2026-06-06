<?php
declare(strict_types=1);

require_once __DIR__ . '/CategoriaService.php';
require_once __DIR__ . '/CategoriaDTO.php';

class CategoriaController
{
    private CategoriaService $service;

    public function __construct(mysqli $db)
    {
        $this->service = new CategoriaService($db);
    }

    public function obtenerPorId(int $id): ?CategoriaDTO
    {
        if ($id <= 0) {
            return null;
        }

        return $this->service->obtenerPorId($id);
    }

    public function guardarCategoria(string $nombre, string $descripcion, ?string $imagen = null, ?int $id = null): bool
    {
        return $this->service->guardarCategoria($nombre, $descripcion, $imagen, $id);
    }

    public function gestionarPeticion(string $accion, ?int $id = null): bool
    {
        if ($accion === 'eliminar' && $id !== null && $id > 0) {
            return $this->service->eliminarCategoria($id);
        }

        return false;
    }
}
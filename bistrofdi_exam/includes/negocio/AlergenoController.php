<?php
declare(strict_types=1);

require_once __DIR__ . '/AlergenoService.php';
require_once __DIR__ . '/AlergenoDTO.php';

class AlergenoController
{
    private AlergenoService $service;

    public function __construct(mysqli $db)
    {
        $this->service = new AlergenoService($db);
    }

    public function obtenerPorId(int $id): ?AlergenoDTO
    {
        if ($id <= 0) {
            return null;
        }

        return $this->service->obtenerPorId($id);
    }

    public function guardar(string $nombre, string $descripcion, ?string $imagen = null, ?int $id = null): bool
    {
        return $this->service->guardar($nombre, $descripcion, $imagen, $id);
    }

    public function eliminar(?int $id = null): bool
    {
        return $this->service->eliminar($id);
    }

    public function listar(): array
    {
        return $this->service->listar();
    }
}
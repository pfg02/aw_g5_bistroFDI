<?php
declare(strict_types=1);

require_once __DIR__ . '/../integracion/AlergenoDAO.php';
require_once __DIR__ . '/../negocio/AlergenoDTO.php';

class AlergenoService
{
    private AlergenoDAO $dao;

    public function __construct(mysqli $db)
    {
        $this->dao = new AlergenoDAO($db);
    }

    public function obtenerPorId(int $id): ?AlergenoDTO
    {
        if ($id <= 0) {
            return null;
        }

        return $this->dao->obtenerPorId($id);
    }

    public function listar(): array
    {
        return $this->dao->listar();
    }

    public function guardar(string $nombre, ?string $descripcion, ?string $imagen, ?int $id = null): bool
    {
        $nombre = trim($nombre);
        $descripcion = $descripcion !== null ? trim($descripcion) : null;
        $imagen = $imagen !== null ? trim($imagen) : null;

        if ($nombre === '') {
            return false;
        }

        if (mb_strlen($nombre) > 100) {
            return false;
        }

        if ($descripcion !== null && mb_strlen($descripcion) > 500) {
            return false;
        }

        if ($id !== null && $id <= 0) {
            return false;
        }

        return $this->dao->guardar($nombre, $descripcion, $imagen, $id);
    }

    public function eliminar(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        return $this->dao->eliminar($id);
    }
}
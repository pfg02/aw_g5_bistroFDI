<?php
declare(strict_types=1);

require_once __DIR__ . '/../integracion/CategoriaDAO.php';
require_once __DIR__ . '/../negocio/CategoriaDTO.php';

class CategoriaService
{
    private CategoriaDAO $dao;

    public function __construct(mysqli $db)
    {
        $this->dao = new CategoriaDAO($db);
    }

    public function obtenerPorId(int $id): ?CategoriaDTO
    {
        if ($id <= 0) {
            return null;
        }

        return $this->dao->obtenerPorId($id);
    }

    public function listarTodas(): array
    {
        return $this->dao->listarTodas();
    }

    public function guardarCategoria(string $nombre, ?string $descripcion, ?string $imagen, ?int $id = null): bool
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

    public function eliminarCategoria(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        return $this->dao->eliminar($id);
    }
}
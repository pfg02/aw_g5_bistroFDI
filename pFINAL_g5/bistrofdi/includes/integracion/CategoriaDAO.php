<?php
declare(strict_types=1);

/**
 * Clase de acceso a datos para categorías.
 */
require_once __DIR__ . '/../negocio/CategoriaDTO.php';

class CategoriaDAO
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    public function obtenerPorId(int $id): ?CategoriaDTO
    {
        $sql = 'SELECT id, nombre, descripcion, imagen FROM categorias WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();
        $resultado->free();
        $stmt->close();

        if (!$fila) {
            return null;
        }

        return new CategoriaDTO(
            (int) $fila['id'],
            (string) $fila['nombre'],
            $fila['descripcion'] !== null ? (string) $fila['descripcion'] : null,
            $fila['imagen'] !== null ? (string) $fila['imagen'] : null
        );
    }

    public function listarTodas(): array
    {
        $sql = 'SELECT id, nombre, descripcion, imagen FROM categorias ORDER BY nombre ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $categorias = [];

        while ($fila = $resultado->fetch_assoc()) {
            $categorias[] = new CategoriaDTO(
                (int) $fila['id'],
                (string) $fila['nombre'],
                $fila['descripcion'] !== null ? (string) $fila['descripcion'] : null,
                $fila['imagen'] !== null ? (string) $fila['imagen'] : null
            );
        }

        $resultado->free();
        $stmt->close();

        return $categorias;
    }

    public function guardar(string $nombre, ?string $descripcion, ?string $imagen, ?int $id = null): bool
    {
        if ($id !== null) {
            $sql = 'UPDATE categorias SET nombre = ?, descripcion = ?, imagen = ? WHERE id = ?';
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('sssi', $nombre, $descripcion, $imagen, $id);
        } else {
            $sql = 'INSERT INTO categorias (nombre, descripcion, imagen) VALUES (?, ?, ?)';
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('sss', $nombre, $descripcion, $imagen);
        }

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function eliminar(int $id): bool
    {
        try {
            $sql = 'DELETE FROM categorias WHERE id = ?';
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $id);

            $ok = $stmt->execute();
            $stmt->close();

            return $ok;
        } catch (Throwable $e) {
            return false;
        }
    }
}
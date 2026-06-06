<?php
declare(strict_types=1);



require_once __DIR__ . '/../negocio/AlergenoDTO.php';

class AlergenoDAO
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    public function listar(): array
    {
        $sql = 'SELECT id, nombre, descripcion, imagen FROM alergenos ORDER BY nombre ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $alergenos = [];

        while ($fila = $resultado->fetch_assoc()) {
            $alergenos[] = new AlergenoDTO(
                (int) $fila['id'],
                (string) $fila['nombre'],
                $fila['descripcion'] !== null ? (string) $fila['descripcion'] : null,
                $fila['imagen'] !== null ? (string) $fila['imagen'] : null
            );
        }

        $resultado->free();
        $stmt->close();

        return $alergenos;
    }

   public function obtenerPorId(int $id): ?AlergenoDTO
    {
        $sql = 'SELECT id, nombre, descripcion, imagen FROM alergenos WHERE id = ?';
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

        return new AlergenoDTO(
            (int) $fila['id'],
            (string) $fila['nombre'],
            $fila['descripcion'] !== null ? (string) $fila['descripcion'] : null,
            $fila['imagen'] !== null ? (string) $fila['imagen'] : null
        );
    }

    public function guardar(string $nombre, ?string $descripcion, ?string $imagen, ?int $id = null): bool
    {
        if ($id !== null) {
            $sql = 'UPDATE alergenos SET nombre = ?, descripcion = ?, imagen = ? WHERE id = ?';
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('sssi', $nombre, $descripcion, $imagen, $id);
        } else {
            $sql = 'INSERT INTO alergenos (nombre, descripcion, imagen) VALUES (?, ?, ?)';
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
            $sql = 'DELETE FROM alergenos WHERE id = ?';
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
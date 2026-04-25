<?php
declare(strict_types=1);

/**
 * Clase para el acceso a datos de las ofertas.
 */

require_once __DIR__ . '/../negocio/OfertasDTO.php';

class OfertaDAO
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    /**
     * Lista todas las ofertas.
     */
    public function listarOfertas(): array
    {
        $sql = "SELECT id, nombre, descripcion, fecha_inicio, fecha_fin, descuento_porcentaje 
                FROM ofertas 
                ORDER BY fecha_inicio DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = $stmt->get_result();
        $ofertas = [];

        while ($row = $result->fetch_assoc()) {
            $ofertas[] = $this->mapearOferta($row);
        }

        $result->free();
        $stmt->close();

        return $ofertas;
    }

    /**
     * Obtiene una oferta por su ID.
     */
    public function obtenerPorId(int $id): ?OfertaDTO
    {
        $sql = "SELECT id, nombre, descripcion, fecha_inicio, fecha_fin, descuento_porcentaje 
                FROM ofertas 
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $result->free();
        $stmt->close();

        return $row ? $this->mapearOferta($row) : null;
    }

    /**
     * Crea una nueva oferta y sus productos asociados.
     */
    public function crearOferta(OfertaDTO $oferta): bool
    {
        $this->db->begin_transaction();

        try {
            $sql = "INSERT INTO ofertas 
                    (nombre, descripcion, fecha_inicio, fecha_fin, descuento_porcentaje) 
                    VALUES (?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($sql);

            $nombre = $oferta->getNombre();
            $descripcion = $oferta->getDescripcion();
            $fechaInicio = $oferta->getFechaInicio();
            $fechaFin = $oferta->getFechaFin();
            $descuentoPorcentaje = (float) $oferta->getDescuentoPorcentaje();

            $stmt->bind_param(
                'ssssd',
                $nombre,
                $descripcion,
                $fechaInicio,
                $fechaFin,
                $descuentoPorcentaje
            );

            $stmt->execute();
            $idOferta = (int) $this->db->insert_id;
            $stmt->close();

            $this->guardarProductosOferta($idOferta, $oferta->getProductos());

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Actualiza una oferta existente y reemplaza sus productos asociados.
     */
    public function actualizarOferta(OfertaDTO $oferta): bool
    {
        $idOferta = (int) $oferta->getId();

        if ($idOferta <= 0) {
            return false;
        }

        $this->db->begin_transaction();

        try {
            $sql = "UPDATE ofertas
                    SET nombre = ?,
                        descripcion = ?,
                        fecha_inicio = ?,
                        fecha_fin = ?,
                        descuento_porcentaje = ?
                    WHERE id = ?";

            $stmt = $this->db->prepare($sql);

            $nombre = $oferta->getNombre();
            $descripcion = $oferta->getDescripcion();
            $fechaInicio = $oferta->getFechaInicio();
            $fechaFin = $oferta->getFechaFin();
            $descuentoPorcentaje = (float) $oferta->getDescuentoPorcentaje();

            $stmt->bind_param(
                'ssssdi',
                $nombre,
                $descripcion,
                $fechaInicio,
                $fechaFin,
                $descuentoPorcentaje,
                $idOferta
            );

            $stmt->execute();
            $stmt->close();

            $sqlDelete = "DELETE FROM ofertas_productos WHERE oferta_id = ?";
            $stmtDelete = $this->db->prepare($sqlDelete);
            $stmtDelete->bind_param('i', $idOferta);
            $stmtDelete->execute();
            $stmtDelete->close();

            $this->guardarProductosOferta($idOferta, $oferta->getProductos());

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Obtiene los productos y cantidades de una oferta específica.
     */
    public function obtenerProductosDeOferta(int $idOferta): array
    {
        $sql = "SELECT op.producto_id, op.cantidad, p.nombre, p.precio_base, p.iva 
                FROM ofertas_productos op
                INNER JOIN productos p ON op.producto_id = p.id
                WHERE op.oferta_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $idOferta);
        $stmt->execute();

        $result = $stmt->get_result();
        $productos = [];

        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }

        $result->free();
        $stmt->close();

        return $productos;
    }

    /**
     * Borra una oferta.
     */
    public function borrarOferta(int $id): bool
    {
        $sql = "DELETE FROM ofertas WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);

        $exito = $stmt->execute();

        $stmt->close();

        return $exito;
    }

    /**
     * Obtiene ofertas activas según la fecha actual.
     */
    public function obtenerOfertasActivas(): array
    {
        $hoy = date('Y-m-d H:i:s');

        $sql = "SELECT id, nombre, descripcion, fecha_inicio, fecha_fin, descuento_porcentaje 
                FROM ofertas 
                WHERE fecha_inicio <= ? AND fecha_fin >= ?
                ORDER BY fecha_inicio DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ss', $hoy, $hoy);
        $stmt->execute();

        $result = $stmt->get_result();
        $ofertas = [];

        while ($row = $result->fetch_assoc()) {
            $ofertas[] = $this->mapearOferta($row);
        }

        $result->free();
        $stmt->close();

        return $ofertas;
    }

    private function guardarProductosOferta(int $idOferta, array $productos): void
    {
        $sql = "INSERT INTO ofertas_productos 
                (oferta_id, producto_id, cantidad) 
                VALUES (?, ?, ?)";

        $stmt = $this->db->prepare($sql);

        foreach ($productos as $producto) {
            $idProducto = (int) ($producto['producto_id'] ?? 0);
            $cantidad = (int) ($producto['cantidad'] ?? 1);

            if ($idProducto <= 0 || $cantidad <= 0) {
                continue;
            }

            $stmt->bind_param('iii', $idOferta, $idProducto, $cantidad);
            $stmt->execute();
        }

        $stmt->close();
    }

    private function mapearOferta(array $row): OfertaDTO
    {
        $oferta = new OfertaDTO();

        $oferta->setId((int) $row['id']);
        $oferta->setNombre((string) $row['nombre']);
        $oferta->setDescripcion((string) ($row['descripcion'] ?? ''));
        $oferta->setFechaInicio((string) $row['fecha_inicio']);
        $oferta->setFechaFin((string) $row['fecha_fin']);
        $oferta->setDescuentoPorcentaje((float) $row['descuento_porcentaje']);

        return $oferta;
    }
}
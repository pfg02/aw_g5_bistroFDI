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
     * Lista las ofertas activas para gestión.
     * No muestra las ofertas borradas lógicamente con activa = 0.
     */
    public function listarOfertas(): array
    {
        $sql = "SELECT id, nombre, descripcion, fecha_inicio, fecha_fin, descuento_porcentaje
                FROM ofertas
                WHERE activa = 1
                ORDER BY fecha_inicio DESC, id DESC";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return [];
        }

        $stmt->execute();

        $result = $stmt->get_result();
        $ofertas = [];

        while ($row = $result->fetch_assoc()) {
            $oferta = $this->mapearOferta($row);
            $oferta->setProductos($this->obtenerProductosDeOferta((int) $row['id']));
            $ofertas[] = $oferta;
        }

        $result->free();
        $stmt->close();

        return $ofertas;
    }

    /**
     * Obtiene una oferta activa por su ID.
     */
    public function obtenerPorId(int $id): ?OfertaDTO
    {
        $sql = "SELECT id, nombre, descripcion, fecha_inicio, fecha_fin, descuento_porcentaje
                FROM ofertas
                WHERE id = ?
                  AND activa = 1";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $result->free();
        $stmt->close();

        if (!$row) {
            return null;
        }

        $oferta = $this->mapearOferta($row);
        $oferta->setProductos($this->obtenerProductosDeOferta($id));

        return $oferta;
    }

    public function buscarPorId(int $id): ?OfertaDTO
    {
        return $this->obtenerPorId($id);
    }

    public function obtenerOfertaPorId(int $id): ?OfertaDTO
    {
        return $this->obtenerPorId($id);
    }

    /**
     * Crea una nueva oferta y sus productos asociados.
     */
    public function crearOferta(OfertaDTO $oferta): bool
    {
        $this->db->begin_transaction();

        try {
            $sql = "INSERT INTO ofertas
                    (nombre, descripcion, fecha_inicio, fecha_fin, descuento_porcentaje, activa)
                    VALUES (?, ?, ?, ?, ?, 1)";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new RuntimeException('No se pudo preparar la creación de la oferta.');
            }

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

            if ($idOferta <= 0) {
                throw new RuntimeException('No se pudo obtener el ID de la oferta creada.');
            }

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
                    WHERE id = ?
                      AND activa = 1";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new RuntimeException('No se pudo preparar la actualización de la oferta.');
            }

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

            if (!$stmtDelete) {
                throw new RuntimeException('No se pudo preparar el borrado de productos de la oferta.');
            }

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
        $sql = "SELECT 
                    op.producto_id,
                    op.cantidad,
                    p.nombre,
                    p.precio_base,
                    p.precio_base AS precio,
                    p.iva
                FROM ofertas_productos op
                INNER JOIN productos p ON op.producto_id = p.id
                WHERE op.oferta_id = ?
                ORDER BY op.id ASC";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('i', $idOferta);
        $stmt->execute();

        $result = $stmt->get_result();
        $productos = [];

        while ($row = $result->fetch_assoc()) {
            $productos[] = [
                'producto_id' => (int) $row['producto_id'],
                'cantidad' => (int) $row['cantidad'],
                'nombre' => (string) $row['nombre'],
                'precio_base' => (float) $row['precio_base'],
                'precio' => (float) $row['precio'],
                'iva' => (float) $row['iva'],
            ];
        }

        $result->free();
        $stmt->close();

        return $productos;
    }

    /**
     * Borra una oferta de forma lógica.
     * No elimina físicamente la fila para no romper históricos.
     */
    public function borrarOferta(int $id): bool
    {
        $sql = "UPDATE ofertas 
                SET activa = 0 
                WHERE id = ?
                  AND activa = 1";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('i', $id);

        $exito = $stmt->execute();
        $filasAfectadas = $stmt->affected_rows;

        $stmt->close();

        return $exito && $filasAfectadas > 0;
    }

    public function eliminarOferta(int $id): bool
    {
        return $this->borrarOferta($id);
    }

    public function eliminar(int $id): bool
    {
        return $this->borrarOferta($id);
    }

    /**
     * Obtiene ofertas activas para el carrito/cliente.
     *
     * Usa CURDATE() para comparar solo por fecha, no por hora.
     * Así una oferta con fecha_inicio = hoy y fecha_fin = hoy estará activa durante todo el día.
     */
    public function obtenerOfertasActivas(): array
    {
        $sql = "SELECT id, nombre, descripcion, fecha_inicio, fecha_fin, descuento_porcentaje
                FROM ofertas
                WHERE activa = 1
                  AND DATE(fecha_inicio) <= CURDATE()
                  AND DATE(fecha_fin) >= CURDATE()
                ORDER BY fecha_inicio DESC, id DESC";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return [];
        }

        $stmt->execute();

        $result = $stmt->get_result();
        $ofertas = [];

        while ($row = $result->fetch_assoc()) {
            $oferta = $this->mapearOferta($row);
            $oferta->setProductos($this->obtenerProductosDeOferta((int) $row['id']));
            $ofertas[] = $oferta;
        }

        $result->free();
        $stmt->close();

        return $ofertas;
    }

    /**
     * Comprueba si una oferta concreta sigue activa.
     */
    public function esOfertaActiva(OfertaDTO $oferta): bool
    {
        $idOferta = (int) $oferta->getId();

        if ($idOferta <= 0) {
            return false;
        }

        $sql = "SELECT id
                FROM ofertas
                WHERE id = ?
                  AND activa = 1
                  AND DATE(fecha_inicio) <= CURDATE()
                  AND DATE(fecha_fin) >= CURDATE()
                LIMIT 1";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('i', $idOferta);
        $stmt->execute();

        $result = $stmt->get_result();
        $activa = $result->num_rows > 0;

        $result->free();
        $stmt->close();

        return $activa;
    }

    /**
     * Registra en la tabla puente qué oferta se usó en qué pedido.
     */
    public function vincularPedidoOferta(int $idPedido, int $idOferta, int $veces, float $descuento): bool
    {
        $sql = "INSERT INTO pedidos_ofertas
                (pedido_id, oferta_id, veces_aplicada, descuento_aplicado)
                VALUES (?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('iiid', $idPedido, $idOferta, $veces, $descuento);

        $exito = $stmt->execute();

        $stmt->close();

        return $exito;
    }

    /**
     * Obtiene todas las ofertas que se aplicaron a un pedido específico.
     */
    public function obtenerOfertasDePedido(int $idPedido): array
    {
        $sql = "SELECT 
                    po.oferta_id,
                    o.nombre,
                    po.veces_aplicada,
                    po.descuento_aplicado
                FROM pedidos_ofertas po
                INNER JOIN ofertas o ON po.oferta_id = o.id
                WHERE po.pedido_id = ?";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('i', $idPedido);
        $stmt->execute();

        $result = $stmt->get_result();
        $ofertasAplicadas = [];

        while ($row = $result->fetch_assoc()) {
            $ofertasAplicadas[] = [
                'id' => (int) $row['oferta_id'],
                'nombre' => (string) $row['nombre'],
                'veces_aplicada' => (int) $row['veces_aplicada'],
                'descuento' => (float) $row['descuento_aplicado'],
            ];
        }

        $result->free();
        $stmt->close();

        return $ofertasAplicadas;
    }

    private function guardarProductosOferta(int $idOferta, array $productos): void
    {
        $sql = "INSERT INTO ofertas_productos
                (oferta_id, producto_id, cantidad)
                VALUES (?, ?, ?)";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            throw new RuntimeException('No se pudo preparar el guardado de productos de la oferta.');
        }

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
        $oferta->setProductos([]);

        return $oferta;
    }
}
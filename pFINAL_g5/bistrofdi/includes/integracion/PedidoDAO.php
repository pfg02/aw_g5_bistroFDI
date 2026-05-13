<?php
declare(strict_types=1);

require_once __DIR__ . '/../negocio/PedidoDTO.php';

class PedidoDAO
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    public function guardarPedido(PedidoDTO $pedidoDTO): int
    {
        $this->db->begin_transaction();

        try {
            $clienteId = (int) $pedidoDTO->getClienteId();
            $tipo = (string) $pedidoDTO->getTipo();
            $estado = (string) $pedidoDTO->getEstado();
            $fecha = (string) $pedidoDTO->getFecha();
            $total = (float) $pedidoDTO->getTotal();
            $descuentoTotal = (float) $pedidoDTO->getDescuentoTotal();
            $totalSinDescuento = (float) $pedidoDTO->getTotalSinDescuento();

            $sqlNum = "SELECT MAX(numero_pedido) AS max_num
                       FROM pedidos";
            $stmtNum = $this->db->prepare($sqlNum);
            $stmtNum->execute();

            $rsNum = $stmtNum->get_result();
            $filaNum = $rsNum->fetch_assoc();
            $rsNum->free();
            $stmtNum->close();

            $numeroPedido = ($filaNum !== null && $filaNum['max_num'] !== null)
                ? ((int) $filaNum['max_num'] + 1)
                : 1;

            $sqlPedido = "INSERT INTO pedidos
                            (cliente_id, numero_pedido, tipo, estado, fecha, total, descuento_total, total_sin_descuento)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $stmtPedido = $this->db->prepare($sqlPedido);
            $stmtPedido->bind_param(
                'iisssddd',
                $clienteId,
                $numeroPedido,
                $tipo,
                $estado,
                $fecha,
                $total,
                $descuentoTotal,
                $totalSinDescuento
            );
            $stmtPedido->execute();

            $pedidoId = (int) $this->db->insert_id;
            $stmtPedido->close();

            /*
             * Todos los productos empiezan como preparado = 0.
             *
             * Si requiere_cocina = 1, lo preparará cocina.
             * Si requiere_cocina = 0, lo servirá sala uno a uno.
             */
            $productos = $pedidoDTO->getProductos();

            $sqlProductos = "INSERT INTO pedido_productos (pedido_id, producto_id, cantidad, preparado)
                             SELECT ?, p.id, ?, 0
                             FROM productos p
                             WHERE p.id = ?";

            $stmtProductos = $this->db->prepare($sqlProductos);

            foreach ($productos as $productoId => $cantidad) {
                $productoId = (int) $productoId;
                $cantidad = (int) $cantidad;

                $stmtProductos->bind_param('iii', $pedidoId, $cantidad, $productoId);
                $stmtProductos->execute();
            }

            $stmtProductos->close();

            $this->db->commit();
            return $pedidoId;
        } catch (Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function buscarPedido(int $idPedido): ?PedidoDTO
    {
        $sql = "SELECT p.*,
                       u.nombre AS nombre_cliente,
                       u.apellidos AS apellidos_cliente,
                       c.nombre AS nombre_cocinero,
                       c.apellidos AS apellidos_cocinero,
                       c.avatar AS avatar_cocinero
                FROM pedidos p
                INNER JOIN usuarios u ON p.cliente_id = u.id
                LEFT JOIN usuarios c ON p.cocinero_id = c.id
                WHERE p.id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $idPedido);
        $stmt->execute();

        $rs = $stmt->get_result();
        $fila = $rs->fetch_assoc();

        $rs->free();
        $stmt->close();

        if (!$fila) {
            return null;
        }

        return $this->crearPedidoDTODesdeFila($fila);
    }

    public function actualizarEstado(int $idPedido, string $nuevoEstado): bool
    {
        $sql = "UPDATE pedidos SET estado = ? WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('si', $nuevoEstado, $idPedido);

        $exito = $stmt->execute();
        $stmt->close();

        return $exito;
    }

    public function asignarCocinero(int $idPedido, ?int $idCocinero, string $nuevoEstado): bool
    {
        $sql = "UPDATE pedidos
                SET cocinero_id = ?, estado = ?
                WHERE id = ?";

        if ($idCocinero !== null) {
            $sql .= " AND estado = 'En preparación'";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('isi', $idCocinero, $nuevoEstado, $idPedido);
        $stmt->execute();

        $filas = $stmt->affected_rows;
        $stmt->close();

        return $filas > 0;
    }

    public function obtenerPedidoActivoDeCocinero(int $idCocinero): ?PedidoDTO
    {
        $sql = "SELECT p.*,
                       u.nombre AS nombre_cliente,
                       u.apellidos AS apellidos_cliente,
                       c.nombre AS nombre_cocinero,
                       c.apellidos AS apellidos_cocinero,
                       c.avatar AS avatar_cocinero
                FROM pedidos p
                INNER JOIN usuarios u ON p.cliente_id = u.id
                LEFT JOIN usuarios c ON p.cocinero_id = c.id
                WHERE p.cocinero_id = ?
                  AND p.estado = 'Cocinando'
                ORDER BY p.fecha ASC
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $idCocinero);
        $stmt->execute();

        $rs = $stmt->get_result();
        $fila = $rs->fetch_assoc();

        $rs->free();
        $stmt->close();

        if (!$fila) {
            return null;
        }

        return $this->crearPedidoDTODesdeFila($fila);
    }

    public function obtenerPedidosPorCliente(int $idCliente): array
    {
        $sql = "SELECT p.*,
                       c.nombre AS nombre_cocinero,
                       c.apellidos AS apellidos_cocinero,
                       c.avatar AS avatar_cocinero
                FROM pedidos p
                LEFT JOIN usuarios c ON p.cocinero_id = c.id
                WHERE p.cliente_id = ?
                ORDER BY p.fecha DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $idCliente);
        $stmt->execute();

        $rs = $stmt->get_result();
        $pedidos = [];

        while ($fila = $rs->fetch_assoc()) {
            $pedidos[] = $this->crearPedidoDTODesdeFila($fila);
        }

        $rs->free();
        $stmt->close();

        return $pedidos;
    }

    public function obtenerPedidosActivos(): array
    {
        $sql = "SELECT p.*,
                       u.nombre AS nombre_cliente,
                       u.apellidos AS apellidos_cliente,
                       c.nombre AS nombre_cocinero,
                       c.apellidos AS apellidos_cocinero,
                       c.avatar AS avatar_cocinero
                FROM pedidos p
                INNER JOIN usuarios u ON p.cliente_id = u.id
                LEFT JOIN usuarios c ON p.cocinero_id = c.id
                WHERE p.estado NOT IN ('Entregado', 'Cancelado')
                ORDER BY p.fecha ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $rs = $stmt->get_result();
        $pedidos = [];

        while ($fila = $rs->fetch_assoc()) {
            $pedidos[] = $this->crearPedidoDTODesdeFila($fila);
        }

        $rs->free();
        $stmt->close();

        return $pedidos;
    }

    /*
     * Devuelve todos los productos del pedido:
     * comida, postres, bebidas y cafés.
     *
     * preparado está en pedido_productos.
     * servido_sala está en pedidos.
     */
    public function obtenerProductosDePedido(int $idPedido): array
    {
        $sql = "SELECT p.id AS producto_id,
                       SUM(pp.cantidad) AS cantidad,
                       p.nombre,
                       p.precio_base,
                       p.iva,
                       p.requiere_cocina,
                       MAX(pp.preparado) AS preparado,
                       MAX(ped.servido_sala) AS servido_sala
                FROM pedido_productos pp
                INNER JOIN productos p ON pp.producto_id = p.id
                INNER JOIN pedidos ped ON ped.id = pp.pedido_id
                WHERE pp.pedido_id = ?
                GROUP BY p.id, p.nombre, p.precio_base, p.iva, p.requiere_cocina
                ORDER BY p.id ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $idPedido);
        $stmt->execute();

        $rs = $stmt->get_result();
        $productos = [];

        while ($fila = $rs->fetch_assoc()) {
            $productos[] = [
                'producto_id' => (int) $fila['producto_id'],
                'cantidad' => (int) $fila['cantidad'],
                'nombre' => (string) $fila['nombre'],
                'precio_base' => (float) $fila['precio_base'],
                'iva' => (int) $fila['iva'],
                'requiere_cocina' => (int) $fila['requiere_cocina'],
                'preparado' => (int) $fila['preparado'],
                'servido_sala' => (int) $fila['servido_sala'],
            ];
        }

        $rs->free();
        $stmt->close();

        return $productos;
    }

    public function verPedidosPorEstado(string $estado): array
    {
        $sql = "SELECT p.*,
                       u.nombre AS nombre_cliente,
                       u.apellidos AS apellidos_cliente,
                       c.nombre AS nombre_cocinero,
                       c.apellidos AS apellidos_cocinero,
                       c.avatar AS avatar_cocinero
                FROM pedidos p
                INNER JOIN usuarios u ON p.cliente_id = u.id
                LEFT JOIN usuarios c ON p.cocinero_id = c.id
                WHERE p.estado = ?
                ORDER BY p.fecha ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $estado);
        $stmt->execute();

        $rs = $stmt->get_result();
        $pedidos = [];

        while ($fila = $rs->fetch_assoc()) {
            $pedidos[] = $this->crearPedidoDTODesdeFila($fila);
        }

        $rs->free();
        $stmt->close();

        return $pedidos;
    }

    public function eliminarPedido(int $idPedido): bool
    {
        $sql = "DELETE FROM pedidos WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $idPedido);

        $exito = $stmt->execute();
        $stmt->close();

        return $exito;
    }

    /*
     * Marca como preparado un producto de cocina.
     * No afecta a bebidas/cafés.
     */
    public function marcarProductoComoPreparado(int $idPedido, int $idProducto): bool
    {
        $sql = "UPDATE pedido_productos pp
                INNER JOIN productos p ON pp.producto_id = p.id
                SET pp.preparado = 1
                WHERE pp.pedido_id = ?
                  AND pp.producto_id = ?
                  AND p.requiere_cocina = 1
                  AND pp.preparado = 0";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ii', $idPedido, $idProducto);

        $exito = $stmt->execute();
        $filas = $stmt->affected_rows;

        $stmt->close();

        return $exito && $filas > 0;
    }

    /*
     * Comprueba si todos los productos de cocina están preparados.
     * Las bebidas/cafés no bloquean el paso a sala.
     */
    public function todosProductosCocinaPreparados(int $idPedido): bool
    {
        $sql = "SELECT COUNT(*) AS pendientes
                FROM pedido_productos pp
                INNER JOIN productos p ON pp.producto_id = p.id
                WHERE pp.pedido_id = ?
                  AND p.requiere_cocina = 1
                  AND pp.preparado = 0";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $idPedido);
        $stmt->execute();

        $rs = $stmt->get_result();
        $fila = $rs->fetch_assoc();

        $rs->free();
        $stmt->close();

        return ((int) ($fila['pendientes'] ?? 0)) === 0;
    }

    /*
     * Marca una bebida/café como servido por sala.
     *
     * Hace dos cosas:
     * 1. Marca solo ese producto como preparado = 1.
     * 2. Marca el pedido como servido_sala = 1.
     *
     * Desde ese momento el cliente ya no debería poder cancelar.
     */
    public function marcarProductoServidoSala(int $idPedido, int $idProducto): bool
    {
        $this->db->begin_transaction();

        try {
            $sqlProducto = "UPDATE pedido_productos pp
                            INNER JOIN productos p ON pp.producto_id = p.id
                            SET pp.preparado = 1
                            WHERE pp.pedido_id = ?
                              AND pp.producto_id = ?
                              AND p.requiere_cocina = 0
                              AND pp.preparado = 0";

            $stmtProducto = $this->db->prepare($sqlProducto);
            $stmtProducto->bind_param('ii', $idPedido, $idProducto);
            $stmtProducto->execute();

            $filasProducto = $stmtProducto->affected_rows;
            $stmtProducto->close();

            if ($filasProducto <= 0) {
                $this->db->rollback();
                return false;
            }

            $sqlPedido = "UPDATE pedidos
                          SET servido_sala = 1
                          WHERE id = ?";

            $stmtPedido = $this->db->prepare($sqlPedido);
            $stmtPedido->bind_param('i', $idPedido);
            $stmtPedido->execute();
            $stmtPedido->close();

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /*
     * Método opcional para marcar el pedido completo como servido.
     * Lo dejo por si alguna pantalla lo usa.
     */
    public function marcarPedidoServidoSala(int $idPedido): bool
    {
        $sql = "UPDATE pedidos
                SET servido_sala = 1
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $idPedido);

        $exito = $stmt->execute();
        $filas = $stmt->affected_rows;

        $stmt->close();

        return $exito && $filas > 0;
    }

    private function crearPedidoDTODesdeFila(array $fila): PedidoDTO
    {
        $pedido = new PedidoDTO();

        $pedido->setId(isset($fila['id']) ? (int) $fila['id'] : null);
        $pedido->setClienteId(isset($fila['cliente_id']) ? (int) $fila['cliente_id'] : null);
        $pedido->setCocineroId(isset($fila['cocinero_id']) ? (int) $fila['cocinero_id'] : null);
        $pedido->setNumeroPedido(isset($fila['numero_pedido']) ? (int) $fila['numero_pedido'] : null);
        $pedido->setTipo($fila['tipo'] ?? null);
        $pedido->setEstado($fila['estado'] ?? null);
        $pedido->setFecha($fila['fecha'] ?? null);
        $pedido->setTotal(isset($fila['total']) ? (float) $fila['total'] : 0.0);
        $pedido->setDescuentoTotal(isset($fila['descuento_total']) ? (float) $fila['descuento_total'] : 0.0);
        $pedido->setTotalSinDescuento(isset($fila['total_sin_descuento']) ? (float) $fila['total_sin_descuento'] : 0.0);

        $pedido->setNombreCliente($fila['nombre_cliente'] ?? null);
        $pedido->setApellidosCliente($fila['apellidos_cliente'] ?? null);
        $pedido->setNombreCocinero($fila['nombre_cocinero'] ?? null);
        $pedido->setApellidosCocinero($fila['apellidos_cocinero'] ?? null);
        $pedido->setAvatarCocinero($fila['avatar_cocinero'] ?? null);

        if (method_exists($pedido, 'setServidoSala')) {
            $pedido->setServidoSala(isset($fila['servido_sala']) ? (int) $fila['servido_sala'] : 0);
        }

        return $pedido;
    }
}
?>
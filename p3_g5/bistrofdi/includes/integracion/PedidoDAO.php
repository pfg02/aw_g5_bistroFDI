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

            $productos = $pedidoDTO->getProductos();
            $sqlProductos = "INSERT INTO pedido_productos (pedido_id, producto_id, cantidad)
                             VALUES (?, ?, ?)";
            $stmtProductos = $this->db->prepare($sqlProductos);

            foreach ($productos as $productoId => $cantidad) {
                $productoId = (int) $productoId;
                $cantidad = (int) $cantidad;
                $stmtProductos->bind_param('iii', $pedidoId, $productoId, $cantidad);
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

    public function asignarCocinero(int $idPedido, int $idCocinero, string $nuevoEstado): bool
    {
        $sql = "UPDATE pedidos
                SET cocinero_id = ?, estado = ?
                WHERE id = ? AND estado = 'En preparación'";

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
                WHERE p.cocinero_id = ? AND p.estado = 'Cocinando'
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

    public function obtenerProductosDePedido(int $idPedido): array
    {
        $sql = "SELECT p.id AS producto_id,
                       SUM(pp.cantidad) AS cantidad,
                       p.nombre,
                       p.precio_base,
                       p.iva,
                       MAX(pp.preparado) AS preparado
                FROM pedido_productos pp
                INNER JOIN productos p ON pp.producto_id = p.id
                WHERE pp.pedido_id = ?
                GROUP BY p.id, p.nombre, p.precio_base, p.iva";

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
                'preparado' => (int) $fila['preparado'],
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

    public function marcarProductoComoPreparado(int $idPedido, int $idProducto): bool
    {
        $sql = "UPDATE pedido_productos
                SET preparado = 1
                WHERE pedido_id = ? AND producto_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ii', $idPedido, $idProducto);

        $exito = $stmt->execute();
        $stmt->close();

        return $exito;
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

        return $pedido;
    }
}
?>
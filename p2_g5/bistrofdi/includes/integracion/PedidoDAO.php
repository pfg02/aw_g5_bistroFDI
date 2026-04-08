<?php

require_once __DIR__ . '/../negocio/PedidoDTO.php';
require_once __DIR__ . '/../config.php';

class PedidoDAO
{
    public function guardarPedido(PedidoDTO $pedidoDTO): int
    {
        $conn = obtenerConexionBD();
        $conn->begin_transaction();

        try {
            $clienteId = $pedidoDTO->getClienteId();
            $tipo = $pedidoDTO->getTipo();
            $estado = $pedidoDTO->getEstado();
            $fecha = $pedidoDTO->getFecha();
            $total = $pedidoDTO->getTotal();

            $hoy = date('Y-m-d');
            $sqlNum = "SELECT MAX(numero_pedido) as max_num FROM pedidos WHERE DATE(fecha) = ?";
            $stmtNum = $conn->prepare($sqlNum);
            $stmtNum->bind_param("s", $hoy);
            $stmtNum->execute();
            $resNum = $stmtNum->get_result()->fetch_assoc();
            $numeroPedido = ($resNum['max_num'] !== null) ? ((int)$resNum['max_num'] + 1) : 1;
            $stmtNum->close();

            $sqlPedido = "INSERT INTO pedidos (cliente_id, numero_pedido, tipo, estado, fecha, total)
                          VALUES (?, ?, ?, ?, ?, ?)";
            $stmtPedido = $conn->prepare($sqlPedido);
            $stmtPedido->bind_param("iisssd", $clienteId, $numeroPedido, $tipo, $estado, $fecha, $total);
            $stmtPedido->execute();
            $pedidoId = $conn->insert_id;
            $stmtPedido->close();

            $productos = $pedidoDTO->getProductos();
            $sqlProductos = "INSERT INTO pedido_productos (pedido_id, producto_id, cantidad) VALUES (?, ?, ?)";
            $stmtProductos = $conn->prepare($sqlProductos);

            foreach ($productos as $productoId => $cantidad) {
                $stmtProductos->bind_param("iii", $pedidoId, $productoId, $cantidad);
                $stmtProductos->execute();
            }
            $stmtProductos->close();

            $conn->commit();
            return $pedidoId;
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    }

    public function buscarPedido(int $idPedido): ?array
    {
        $conn = obtenerConexionBD();

        $sql = "SELECT p.*, u.nombre AS nombre_cliente, u.apellidos AS apellidos_cliente
                FROM pedidos p
                INNER JOIN usuarios u ON p.cliente_id = u.id
                WHERE p.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idPedido);
        $stmt->execute();
        $pedido = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $pedido ?: null;
    }

    public function obtenerLineasPedido(int $idPedido): array
    {
        $conn = obtenerConexionBD();

        $sql = "SELECT pp.producto_id, pp.cantidad, p.nombre, p.descripcion, p.imagen, p.precio_base, p.iva
                FROM pedido_productos pp
                INNER JOIN productos p ON pp.producto_id = p.id
                WHERE pp.pedido_id = ?
                ORDER BY p.nombre ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idPedido);
        $stmt->execute();
        $result = $stmt->get_result();

        $lineas = [];
        while ($row = $result->fetch_assoc()) {
            $lineas[] = $row;
        }
        $stmt->close();

        return $lineas;
    }

    public function actualizarEstado(int $idPedido, string $nuevoEstado): bool
    {
        $conn = obtenerConexionBD();

        $sql = "UPDATE pedidos SET estado = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $nuevoEstado, $idPedido);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function obtenerPedidosPorCliente(int $idCliente): array
    {
        $conn = obtenerConexionBD();

        $sql = "SELECT * FROM pedidos WHERE cliente_id = ? ORDER BY fecha DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idCliente);
        $stmt->execute();
        $result = $stmt->get_result();

        $pedidos = [];
        while ($row = $result->fetch_assoc()) {
            $pedidos[] = $row;
        }
        $stmt->close();

        return $pedidos;
    }

    public function obtenerPedidosPorEstado(string $estado): array
    {
        $conn = obtenerConexionBD();

        $sql = "SELECT p.*, u.nombre AS nombre_cliente, u.apellidos AS apellidos_cliente, u.avatar
                FROM pedidos p
                INNER JOIN usuarios u ON p.cliente_id = u.id
                WHERE p.estado = ?
                ORDER BY p.fecha ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $estado);
        $stmt->execute();
        $result = $stmt->get_result();

        $pedidos = [];
        while ($row = $result->fetch_assoc()) {
            $pedidos[] = $row;
        }
        $stmt->close();

        return $pedidos;
    }

    public function borrarPedidoCliente(int $idPedido, int $clienteId): bool
    {
        $conn = obtenerConexionBD();

        $sql = "DELETE FROM pedidos WHERE id = ? AND cliente_id = ? AND estado IN ('Nuevo', 'Recibido')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $idPedido, $clienteId);
        $ok = $stmt->execute();
        $afectadas = $stmt->affected_rows;
        $stmt->close();

        return $ok && $afectadas > 0;
    }
}
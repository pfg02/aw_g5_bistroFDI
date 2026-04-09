<?php

require_once __DIR__ . '/../negocio/PedidoDTO.php';

class PedidoDAO {

	private $db;

    public function __construct() {
       	global $db;
		$this->db = $db;
    }

	public function guardarPedido($pedidoDTO) {

		$this->db->begin_transaction();

		try {
			$clienteId = $pedidoDTO->getClienteId();
			$tipo = $pedidoDTO->getTipo();
			$estado = $pedidoDTO->getEstado();
			$fecha = $pedidoDTO->getFecha();
			$total = $pedidoDTO->getTotal();

			$hoy = date('Y-m-d');
			$sqlNum = "SELECT MAX(numero_pedido) as max_num FROM pedidos WHERE DATE(fecha) = ?";
			$stmtNum = $this->db->prepare($sqlNum);
			$stmtNum->bind_param("s", $hoy);
			$stmtNum->execute();

			$resultNum = $stmtNum->get_result();
			$resNum = $resultNum->fetch_assoc();
			$resultNum->free();

			$numeroPedido = ($resNum['max_num'] !== null) ? $resNum['max_num'] + 1 : 1;
			$stmtNum->close();

			$sqlPedido = "INSERT INTO pedidos (cliente_id, numero_pedido, tipo, estado, fecha, total) VALUES (?, ?, ?, ?, ?, ?)";
			$stmtPedido = $this->db->prepare($sqlPedido);
			$stmtPedido->bind_param("iisssd", $clienteId, $numeroPedido, $tipo, $estado, $fecha, $total);
			$stmtPedido->execute();

			$pedidoId = $this->db->insert_id;
			$stmtPedido->close();

			$productos = $pedidoDTO->getProductos();
			$sqlProductos = "INSERT INTO pedido_productos (pedido_id, producto_id, cantidad) VALUES (?, ?, ?)";
			$stmtProductos = $this->db->prepare($sqlProductos);

			foreach ($productos as $productoId => $cantidad) {
				$stmtProductos->bind_param("iii", $pedidoId, $productoId, $cantidad);
				$stmtProductos->execute();
			}
			$stmtProductos->close();

			$this->db->commit();
			return $pedidoId;

		} catch (Exception $e) {
			$this->db->rollback();
			throw $e;
		}
	}

	public function buscarPedido($idPedido) {

		$sql = "SELECT p.*, 
                       u.nombre AS nombre_cliente, u.apellidos AS apellidos_cliente,
                       c.nombre AS nombre_cocinero, c.apellidos AS apellidos_cocinero, c.avatar AS avatar_cocinero
                FROM pedidos p
                INNER JOIN usuarios u ON p.cliente_id = u.id
                LEFT JOIN usuarios c ON p.cocinero_id = c.id
                WHERE p.id = ?";
		$stmt = $this->db->prepare($sql);
		$stmt->bind_param("i", $idPedido);
		$stmt->execute();
		
		$result = $stmt->get_result();
		$pedido = $result->fetch_assoc();
		$result->free();

		$stmt->close();

		return $pedido;
	}

	public function actualizarEstado($idPedido, $nuevoEstado) {

		$sql = "UPDATE pedidos SET estado = ? WHERE id = ?";
		$stmt = $this->db->prepare($sql);
		$stmt->bind_param("si", $nuevoEstado, $idPedido);
		
		$exito = $stmt->execute();
		$stmt->close();

		return $exito;
	}

	public function asignarCocinero($idPedido, $idCocinero, $nuevoEstado) {

		$sql = "UPDATE pedidos 
                SET cocinero_id = ?, estado = ? 
                WHERE id = ? AND estado = 'En preparación'";
		$stmt = $this->db->prepare($sql);
		$stmt->bind_param("isi", $idCocinero, $nuevoEstado, $idPedido);
		$stmt->execute();

		$filas = $stmt->affected_rows;
		$stmt->close();

		return $filas > 0;
	}

	public function obtenerPedidoActivoDeCocinero($idCocinero) {
		$sql = "SELECT p.*, 
                       u.nombre AS nombre_cliente, u.apellidos AS apellidos_cliente,
                       c.nombre AS nombre_cocinero, c.apellidos AS apellidos_cocinero, c.avatar AS avatar_cocinero
                FROM pedidos p
                INNER JOIN usuarios u ON p.cliente_id = u.id
                LEFT JOIN usuarios c ON p.cocinero_id = c.id
                WHERE p.cocinero_id = ? AND p.estado = 'Cocinando'
                ORDER BY p.fecha ASC
                LIMIT 1";
		$stmt = $this->db->prepare($sql);
		$stmt->bind_param("i", $idCocinero);
		$stmt->execute();

		$result = $stmt->get_result();
		$pedido = $result->fetch_assoc();
		$result->free();

		$stmt->close();

		return $pedido ?: null;
	}

	public function obtenerPedidosPorCliente($idCliente) {

		$sql = "SELECT p.*, 
                       c.nombre AS nombre_cocinero, c.apellidos AS apellidos_cocinero, c.avatar AS avatar_cocinero
                FROM pedidos p
                LEFT JOIN usuarios c ON p.cocinero_id = c.id
                WHERE p.cliente_id = ? 
                ORDER BY p.fecha DESC";
		$stmt = $this->db->prepare($sql);
		$stmt->bind_param("i", $idCliente);
		$stmt->execute();
		
		$result = $stmt->get_result();
		$pedidos = [];

		while ($row = $result->fetch_assoc()) {
			$pedidos[] = $row;
		}

		$result->free();
		$stmt->close();
		
		return $pedidos;
	}

	public function obtenerPedidosActivos() {
		$sql = "SELECT p.*, 
                       u.nombre AS nombre_cliente, u.apellidos AS apellidos_cliente,
                       c.nombre AS nombre_cocinero, c.apellidos AS apellidos_cocinero, c.avatar AS avatar_cocinero
                FROM pedidos p 
                INNER JOIN usuarios u ON p.cliente_id = u.id 
                LEFT JOIN usuarios c ON p.cocinero_id = c.id
                WHERE p.estado NOT IN ('Entregado', 'Cancelado') 
                ORDER BY p.fecha ASC";
		$result = $this->db->query($sql);
		
		$pedidos = [];
		if ($result) {
			while ($row = $result->fetch_assoc()) {
				$pedidos[] = $row;
			}
			$result->free();
		}

		return $pedidos;
	}

	public function obtenerProductosDePedido($idPedido) {
		$sql = "SELECT p.id AS producto_id, SUM(pp.cantidad) AS cantidad, p.nombre, p.precio_base, p.iva, MAX(pp.preparado) AS preparado
                FROM pedido_productos pp
                INNER JOIN productos p ON pp.producto_id = p.id
                WHERE pp.pedido_id = ?
                GROUP BY p.id, p.nombre, p.precio_base, p.iva";

		$stmt = $this->db->prepare($sql);
		$stmt->bind_param("i", $idPedido);
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

	public function verPedidosPorEstado($estado) {
        $sql = "SELECT p.*, 
                       u.nombre AS nombre_cliente, u.apellidos AS apellidos_cliente,
                       c.nombre AS nombre_cocinero, c.apellidos AS apellidos_cocinero, c.avatar AS avatar_cocinero
                FROM pedidos p
                INNER JOIN usuarios u ON p.cliente_id = u.id
                LEFT JOIN usuarios c ON p.cocinero_id = c.id
                WHERE p.estado = ? 
                ORDER BY p.fecha ASC";
        $stmt = $this->db->prepare($sql);
        
        $stmt->bind_param("s", $estado);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $pedidos = [];
        
        while ($fila = $result->fetch_assoc()) {
            $pedidos[] = $fila;
        }

        $result->free();
        $stmt->close();

        return $pedidos;
    }

	public function eliminarPedido($idPedido) {
		$sql = "DELETE FROM pedidos WHERE id = ?";
		$stmt = $this->db->prepare($sql);
		$stmt->bind_param("i", $idPedido);
		
		$exito = $stmt->execute();
		$stmt->close();

		return $exito;
	}

	public function marcarProductoComoPreparado($idPedido, $idProducto) {
        $sql = "UPDATE pedido_productos SET preparado = 1 WHERE pedido_id = ? AND producto_id = ?";
        $stmt = $this->db->prepare($sql);
        
        $stmt->bind_param("ii", $idPedido, $idProducto);
        $exito = $stmt->execute();
        $stmt->close();
        
		return $exito;
    }

}
?>
<?php

/**
	* Clase de acceso a datos para pedidos.
*/

require_once __DIR__ . '/../negocio/PedidoDTO.php';

class PedidoDAO {

	private $db;

	// Constructor que recibe la conexión
    public function __construct() {
       	global $db;
		$this->db = $db;
    }


	/**
	* Guarda un nuevo pedido en la base de datos.
	*/

	public function guardarPedido($pedidoDTO) {

		$this->db->begin_transaction();

		try {
			// Extraemos los datos del DTO
			$pedidoId = $pedidoDTO->getId();
			$clienteId = $pedidoDTO->getClienteId();
			$tipo = $pedidoDTO->getTipo();
			$estado = $pedidoDTO->getEstado();
			$fecha = $pedidoDTO->getFecha();
			$total = $pedidoDTO->getTotal();

			// Calcular el numero de pedido diario
			$hoy = date('Y-m-d');
			$sqlNum = "SELECT MAX(numero_pedido) as max_num FROM pedidos WHERE DATE(fecha) = ?";
			$stmtNum = $this->db->prepare($sqlNum);
			$stmtNum->bind_param("s", $hoy);
			$stmtNum->execute();
			$resNum = $stmtNum->get_result()->fetch_assoc();

			// Si hay pedidos hoy, sumamos 1 al máximo. Si no, es el pedido 1 del día.
			$numeroPedido = ($resNum['max_num'] !== null) ? $resNum['max_num'] + 1 : 1;
			$stmtNum->close();

			$sqlPedido = "INSERT INTO pedidos (cliente_id, numero_pedido, tipo, estado, fecha, total) VALUES (?, ?, ?, ?, ?, ?)";
			$stmtPedido = $this->db->prepare($sqlPedido);
			$stmtPedido->bind_param("iisssd", $clienteId, $numeroPedido, $tipo, $estado, $fecha, $total);
			$stmtPedido->execute();

			// Capturamos el ID autonumérico que MySQL ha dado al nuevo pedido
			$pedidoId = $this->db->insert_id;
			$stmtPedido->close();

			// Guardar los productos en la tabla intermedia
			$productos = $pedidoDTO->getProductos();
			$sqlProductos = "INSERT INTO pedido_productos (pedido_id, producto_id, cantidad) VALUES (?, ?, ?)";
			$stmtProductos = $this->db->prepare($sqlProductos);

			foreach ($productos as $productoId => $cantidad) {
				$stmtProductos->bind_param("iii", $pedidoId, $productoId, $cantidad);
				$stmtProductos->execute();
			}
			$stmtProductos->close();

			// Confirmamos los cambios en la BD
			$this->db->commit();

			return $pedidoId;

		} catch (Exception $e) {
			// Si algo falla, revertimos los cambios
			$this->db->rollback();
			throw $e; // Re-lanzamos la excepción para que el controlador pueda manejarla
		}
	}

	/**
	* Busca un pedido por su ID.
	*/
	public function buscarPedido($idPedido) {

		$sql = "SELECT * FROM pedidos WHERE id = ?";
		$stmt = $this->db->prepare($sql);
		$stmt->bind_param("i", $idPedido);
		$stmt->execute();
		
		$result = $stmt->get_result();
		$pedido = $result->fetch_assoc();

		$stmt->close();

		return $pedido;
	}

	/**
	* Actualiza el estado de un pedido existente.
	*/
	public function actualizarEstado($idPedido, $nuevoEstado) {

		$sql = "UPDATE pedidos SET estado = ? WHERE id = ?";
		$stmt = $this->db->prepare($sql);
		$stmt->bind_param("si", $nuevoEstado, $idPedido);
		
		$exito = $stmt->execute();
		$stmt->close();

		return $exito;
	}

	/**
	* Obtiene todo el historial de pedidos de un cliente específico.
	*/
	public function obtenerPedidosPorCliente($idCliente) {

		// Buscar los pedidos del cliente y ordenarlos del más reciente al más antiguo
		$sql = "SELECT * FROM pedidos WHERE cliente_id = ? ORDER BY fecha DESC";
		$stmt = $this->db->prepare($sql);
		$stmt->bind_param("i", $idCliente);
		$stmt->execute();
		
		$result = $stmt->get_result();
		$pedidos = [];

		// Extraemos todas las filas encontradas en un array
		while ($row = $result->fetch_assoc()) {
			$pedidos[] = $row;
		}

		$stmt->close();
		
		return $pedidos;
	}

	/**
	* Obtiene todos los pedidos que no estén entregados o cancelados.
	*/
	public function obtenerPedidosActivos() {
		// Traemos todos los pedidos del más antiguo al más nuevo
		$sql = "SELECT p.*, u.nombre AS nombre_cliente, u.apellidos AS apellidos_cliente 
                FROM pedidos p 
                INNER JOIN usuarios u ON p.cliente_id = u.id 
                WHERE p.estado NOT IN ('Entregado', 'Cancelado') 
                ORDER BY p.fecha ASC";
		$result = $this->db->query($sql);
		
		$pedidos = [];
		while ($row = $result->fetch_assoc()) {
			$pedidos[] = $row;
		}
		return $pedidos;
	}

	/**
	 * Obtiene los productos asociados a un pedido específico, incluyendo detalles como nombre, precio y cantidad.
	 */
	public function obtenerProductosDePedido($idPedido) {
		$sql = "SELECT pp.producto_id, pp.cantidad, p.nombre, p.precio_base, p.iva
				FROM pedido_productos pp
				INNER JOIN productos p ON pp.producto_id = p.id
				WHERE pp.pedido_id = ?
				GROUP BY p.id, p.nombre";
				
		$stmt = $this->db->prepare($sql);
		$stmt->bind_param("i", $idPedido);
		$stmt->execute();
		
		$result = $stmt->get_result();
		$productos = [];

		while ($row = $result->fetch_assoc()) {
			$productos[] = $row;
		}

		$stmt->close();

		return $productos;

	}

	/**
	 * Obtiene todos los pedidos que estén en un estado específico, ordenados del más antiguo al más nuevo.
	 */
	public function verPedidosPorEstado($estado) {
        $sql = "SELECT * FROM pedidos WHERE estado = ? ORDER BY fecha ASC";
        $stmt = $this->db->prepare($sql);
        
        $stmt->bind_param("s", $estado);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $pedidos = [];
        
        while ($fila = $result->fetch_assoc()) {
            $pedidos[] = $fila;
        }

        $stmt->close();

        return $pedidos;
    }
}
?>
<?php

/**
	* Clase de acceso a datos para pedidos.
*/

require_once __DIR__ . '/../negocio/PedidoDTO.php';
require_once __DIR__ . '/../config.php';

class PedidoDAO {

	/**
	* Guarda un nuevo pedido en la base de datos.
	* @param PedidoDTO $pedidoDTO El objeto con los datos del pedido a guardar.
	* @return int El ID del pedido recién creado.
	*/

	public function guardarPedido($pedidoDTO) {

		$conn = obtenerConexionBD();

		$conn->begin_transaction();

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
			$stmtNum = $conn->prepare($sqlNum);
			$stmtNum->bind_param("s", $hoy);
			$stmtNum->execute();
			$resNum = $stmtNum->get_result()->fetch_assoc();

			// Si hay pedidos hoy, sumamos 1 al máximo. Si no, es el pedido 1 del día.
			$numeroPedido = ($resNum['max_num'] !== null) ? $resNum['max_num'] + 1 : 1;
			$stmtNum->close();

			$sqlPedido = "INSERT INTO pedidos (cliente_id, numero_pedido, tipo, estado, fecha, total) VALUES (?, ?, ?, ?, ?, ?)";
			$stmtPedido = $conn->prepare($sqlPedido);
			$stmtPedido->bind_param("iisssd", $clienteId, $numeroPedido, $tipo, $estado, $fecha, $total);
			$stmtPedido->execute();

			// Capturamos el ID autonumérico que MySQL ha dado al nuevo pedido
			$pedidoId = $conn->insert_id;
			$stmtPedido->close();

			// Guardar los productos en la tabla intermedia
			$productos = $pedidoDTO->getProductos();
			$sqlProductos = "INSERT INTO pedido_productos (pedido_id, producto_id, cantidad) VALUES (?, ?, ?)";
			$stmtProductos = $conn->prepare($sqlProductos);

			foreach ($productos as $productoId => $cantidad) {
				$stmtProductos->bind_param("iii", $pedidoId, $productoId, $cantidad);
				$stmtProductos->execute();
			}
			$stmtProductos->close();

			// Confirmamos los cambios en la BD
			$conn->commit();

			return $pedidoId;

		} catch (Exception $e) {
			// Si algo falla, revertimos los cambios
			$conn->rollback();
			throw $e; // Re-lanzamos la excepción para que el controlador pueda manejarla
		}
	}

	/**
	* Busca un pedido por su ID.
	* @param int $idPedido El ID del pedido a buscar.
	* @return array Un array asociativo con los datos del pedido, o null si no se encuentra.
	*/
	public function buscarPedido($idPedido) {

		$conn = obtenerConexionBD();

		$sql = "SELECT * FROM pedidos WHERE id = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("i", $idPedido);
		$stmt->execute();
		
		$result = $stmt->get_result();
		$pedido = $result->fetch_assoc();

		$stmt->close();

		return $pedido;
	}

	/**
	* Actualiza el estado de un pedido existente.
	* * @param int $idPedido El ID autonumérico del pedido.
	* @param string $nuevoEstado El nuevo estado (ej. 'En preparación', 'Terminado').
	* @return bool True si se actualizó correctamente, False en caso de error.
	*/
	public function actualizarEstado($idPedido, $nuevoEstado) {
		
		$conn = obtenerConexionBD();

		$sql = "UPDATE pedidos SET estado = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
		$stmt->bind_param("si", $nuevoEstado, $idPedido);
        
		$exito = $stmt->execute();
		$stmt->close();

		return $exito;
    }

    /**
	* Obtiene todo el historial de pedidos de un cliente específico.
	* * @param int $idCliente El ID autonumérico del usuario.
	* @return array Un array de arrays asociativos con los datos de todos sus pedidos.
	*/
	public function obtenerPedidosPorCliente($idCliente) {

		$conn = obtenerConexionBD();

		// Buscar los pedidos del cliente y ordenarlos del más reciente al más antiguo
		$sql = "SELECT * FROM pedidos WHERE cliente_id = ? ORDER BY fecha DESC";
        $stmt = $conn->prepare($sql);
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
}
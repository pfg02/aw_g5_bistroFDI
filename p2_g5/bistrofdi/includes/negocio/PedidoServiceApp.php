<?php

/**
	* Servicio de negocio para la gestión de pedidos.
*/

require_once __DIR__ . '/../integracion/PedidoDAO.php';
require_once __DIR__ . '/../integracion/ProductoDAO.php';
require_once __DIR__ . '/../config.php';

class PedidoServiceApp {

	private $pedidoDAO;
	private $productoDAO;

	public function __construct() {
		$this->pedidoDAO = new PedidoDAO();
		$this->productoDAO = new ProductoDAO();
	}

	/**
	* Crea un nuevo pedido a partir de un PedidoDTO.
	* Calcula el total del pedido sumando el precio de cada producto por su cantidad.
	* @param PedidoDTO $pedidoDTO El objeto con los datos del pedido a crear.
	* @return int El ID del pedido recién creado.
	*/
	public function crearPedido($pedidoDTO) {
		$productos = $pedidoDTO->getProductos();
		$total = 0;

		foreach ($productos as $idProducto => $cantidad) {
			$producto = $this->productoDAO->obtenerPorId($idProducto);
			if ($producto) {
				$precioBase = $producto->precio; 
                $porcentajeIva = $producto->iva;
				$precioConIva = $precioBase * (1 + ($porcentajeIva / 100));
                $total += $precioConIva * $cantidad;
			}
		}

		// Completamos los datos del pedidoDTO antes de guardarlo
		$pedidoDTO->setTotal($total);
		$pedidoDTO->setEstado("Recibido");
		$pedidoDTO->setFecha(date("Y-m-d H:i:s"));

		return $this->pedidoDAO->guardarPedido($pedidoDTO);
	}

	/**
	* Obtiene los datos de un pedido por su ID.
	* @param int $idPedido El ID del pedido a obtener.
	* @return array Un array asociativo con los datos del pedido, o null si no se encuentra.
	*/
	public function obtenerPedido($idPedido) {
		return $this->pedidoDAO->buscarPedido($idPedido);
	}

	/**
	* Actualiza el estado de un pedido.
	* @param int $idPedido El ID del pedido.
	* @param string $nuevoEstado El nuevo estado.
	* @return bool True si se actualizó correctamente.
	*/
	public function actualizarEstado($idPedido, $nuevoEstado) {
		return $this->pedidoDAO->actualizarEstado($idPedido, $nuevoEstado);
    }
	
	/**
	* Obtiene el historial completo de pedidos de un cliente.
	* @param int $idCliente El ID del cliente.
	* @return array Lista de pedidos.
	*/
	public function obtenerPedidosPorCliente($idCliente) {
		return $this->pedidoDAO->obtenerPedidosPorCliente($idCliente);
	}

	/**
	* Obtiene todos los pedidos que no están entregados ni cancelados.
	* @return array Lista de pedidos.
	*/
	public function obtenerPedidosActivos() {
        return $this->pedidoDAO->obtenerPedidosActivos();
    }

	public function obtenerProductosDePedido($idPedido) {
		return $this->pedidoDAO->obtenerProductosDePedido($idPedido);
	}

	public function verPedidosPorEstado($estado) {
		return $this->pedidoDAO->verPedidosPorEstado($estado);
	}
}
?>
<?php

/**
	* Controlador de pedidos, actúa como intermediario entre la capa de presentación y el servicio de negocio.
*/

require_once __DIR__ . "/PedidoServiceApp.php";
require_once __DIR__ . "/PedidoDTO.php";

class PedidoController {

	private static $instance = null;
	private $service;

	// controlador singleton
	private function __construct() {
		$this->service = new PedidoServiceApp();
	}

	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new PedidoController();
		}
		return self::$instance;
	}

	/**
	* Crea un nuevo pedido a partir de los datos proporcionados por la capa de presentación.
	*/
	public function crearPedido($clienteId, $tipo, $productos) {

		$pedido = new PedidoDTO();

		$pedido->setClienteId($clienteId);
		$pedido->setTipo($tipo);
		$pedido->setProductos($productos);

		return $this->service->crearPedido($pedido);
	}

	/**
	* Obtiene los datos de un pedido por su ID.
	*/
	public function verPedido($idPedido) {
		return $this->service->obtenerPedido($idPedido);
	}

	/**
	* Actualiza el estado de un pedido.
	*/
	public function actualizarEstadoPedido($idPedido, $nuevoEstado) {
		return $this->service->actualizarEstado($idPedido, $nuevoEstado);
    }

	/**
	* Elimina un pedido de forma permanente.
	*/
	public function eliminarPedido($idPedido) {
		return $this->service->eliminarPedido($idPedido);
	}

	/**
	* Obtiene el historial de pedidos de un cliente concreto.
	*/
	public function verPedidosCliente($idCliente) {
		return $this->service->obtenerPedidosPorCliente($idCliente);
	}

	/**
	* Obtiene todos los pedidos que no están entregados ni cancelados.
	*/
	public function verPedidosActivos() {
        return $this->service->obtenerPedidosActivos();
    }

	public function obtenerProductosDePedido($idPedido) {
		return $this->service->obtenerProductosDePedido($idPedido);
	}

	public function verPedidosPorEstado($estado) {
		return $this->service->verPedidosPorEstado($estado);
	}
}
?>
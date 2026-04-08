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
	*/
	public function obtenerPedido($idPedido) {
		return $this->pedidoDAO->buscarPedido($idPedido);
	}

	/**
	* Actualiza el estado de un pedido.
	*/
	public function actualizarEstado($idPedido, $nuevoEstado) {
		return $this->pedidoDAO->actualizarEstado($idPedido, $nuevoEstado);
    }

	/**
	* Elimina un pedido de forma permanente.
	*/
	public function eliminarPedido($idPedido) {
		return $this->pedidoDAO->eliminarPedido($idPedido);
	}
	
	/**
	* Obtiene el historial completo de pedidos de un cliente.
	*/
	public function obtenerPedidosPorCliente($idCliente) {
		return $this->pedidoDAO->obtenerPedidosPorCliente($idCliente);
	}

	/**
	* Obtiene todos los pedidos que no están entregados ni cancelados.
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
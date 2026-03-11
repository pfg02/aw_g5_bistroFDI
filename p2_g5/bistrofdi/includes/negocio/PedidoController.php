<?php

/**
	* Controlador de pedidos, actúa como intermediario entre la capa de presentación y el servicio de negocio.
	* @author Gabriel Omaña
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
	* @param int $clienteId El ID del cliente que realiza el pedido.
	* @param string $tipo El tipo de pedido (por ejemplo, "COMIDA", "BEBIDA").
	* @param array $productos Un array asociativo donde las claves son los IDs de los productos y los valores son las cantidades.
	* @return int El ID del pedido recién creado.
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
	* @param int $idPedido El ID del pedido a obtener.
	* @return array Un array asociativo con los datos del pedido, o null si no se encuentra.
	*/
	public function verPedido($idPedido) {
	return $this->service->obtenerPedido($idPedido);
	}

}
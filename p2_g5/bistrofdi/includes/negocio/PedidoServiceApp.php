<?php

/**
	* Servicio de negocio para la gestión de pedidos.
	* @author Gabriel Omaña
	*/

require_once __DIR__ . '/../integracion/PedidoDAO.php';
require_once __DIR__ . '/../config.php';

class PedidoServiceApp {

	private $dao;

	public function __construct() {
	$this->dao = new PedidoDAO();
	}

	/**
	* Crea un nuevo pedido a partir de un PedidoDTO.
	* Calcula el total del pedido sumando el precio de cada producto por su cantidad.
	* @param PedidoDTO $pedidoDTO El objeto con los datos del pedido a crear.
	* @return int El ID del pedido recién creado.
	*/
	public function crearPedido($pedidoDTO) {

	// lo ideal sería tener un DAO de productos para acceder al precio de los productos...
	$conn = obtenerConexionBD();

	$productos = $pedidoDTO->getProductos();

	$ids = implode(",", array_keys($productos));

	$sql = "SELECT id, precio_base FROM productos WHERE id IN ($ids)";

	$result = $conn->query($sql);

	$precios = [];

	while ($row = $result->fetch_assoc()) {
	$precios[$row["id"]] = $row["precio_base"];
	}

	$total = 0;

	foreach ($productos as $productoId => $cantidad) {
	$total += $precios[$productoId] * $cantidad;
	}

	$pedidoDTO->setTotal($total);
	$pedidoDTO->setEstado("RECIBIDO");
	$pedidoDTO->setFecha(date("Y-m-d H:i:s"));

	return $this->dao->guardarPedido($pedidoDTO);
	}

	/**
	* Obtiene los datos de un pedido por su ID.
	* @param int $idPedido El ID del pedido a obtener.
	* @return array Un array asociativo con los datos del pedido, o null si no se encuentra.
	*/
	public function obtenerPedido($idPedido) {
	return $this->dao->buscarPedido($idPedido);
	}

}
<?php

/**
	* Clase de transferencia de datos para pedidos.
*/

class PedidoDTO {

	private $id;
	private $clienteId;
	private $numeroPedido;
	private $tipo;
	private $estado;
	private $fecha;
	private $productos;
	private $total;

	public function __construct() {
		$this->productos = [];
	}

	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getClienteId() {
		return $this->clienteId;
	}

	public function setClienteId($clienteId) {
		$this->clienteId = $clienteId;
	}

	public function getNumeroPedido() {
		return $this->numeroPedido;
	}

	public function setNumeroPedido($numeroPedido) {
		$this->numeroPedido = $numeroPedido;
	}

	public function getTipo() {
		return $this->tipo;
	}

	public function setTipo($tipo) {
		$this->tipo = $tipo;
	}

	public function getEstado() {
		return $this->estado;
	}

	public function setEstado($estado) {
		$this->estado = $estado;
	}

	public function getFecha() {
		return $this->fecha;
	}

	public function setFecha($fecha) {
		$this->fecha = $fecha;
	}

	public function getProductos() {
		return $this->productos;
	}

	public function setProductos($productos) {
		$this->productos = $productos;
	}

	public function getTotal() {
		return $this->total;
	}

	public function setTotal($total) {
		$this->total = $total;
	}
}
?>
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
		private $subtotal;
		private $descuentoTotal;

		public function __construct() {
			$this->productos = [];
			$this->total = 0.00;
			$this->subtotal = 0.00;
			$this->descuentoTotal = 0.00;
		}

		public function getId() { return $this->id; }
		public function getClienteId() { return $this->clienteId; }
		public function getNumeroPedido() { return $this->numeroPedido; }
		public function getTipo() { return $this->tipo; }
		public function getEstado() { return $this->estado; }
		public function getFecha() { return $this->fecha; }
		public function getProductos() { return $this->productos; }
		public function getTotal() { return $this->total; }
		public function getSubtotal() { return $this->subtotal; }
		public function getDescuentoTotal() { return $this->descuentoTotal; }

		public function setId($id) { $this->id = $id; }
		public function setClienteId($clienteId) { $this->clienteId = $clienteId; }
		public function setNumeroPedido($numeroPedido) { $this->numeroPedido = $numeroPedido; }
		public function setTipo($tipo) { $this->tipo = $tipo; }
		public function setEstado($estado) { $this->estado = $estado; }
		public function setFecha($fecha) { $this->fecha = $fecha; }
		public function setProductos($productos) { $this->productos = $productos; }
		public function setTotal($total) { $this->total = $total; }
		public function setSubtotal($subtotal) { $this->subtotal = $subtotal; }
		public function setDescuentoTotal($descuentoTotal) { $this->descuentoTotal = $descuentoTotal; }
	}
?>
<?php

	class OfertaDTO {
		private $id;
		private $nombre;
		private $descripcion;
		private $fecha_inicio;
		private $fecha_fin;
		private $descuento_porcentaje;
		private $productos; 

		public function __construct() {
        $this->productos = [];
    }

		public function getId() { return $this->id; }
		public function getNombre() { return $this->nombre; }
		public function getDescripcion() { return $this->descripcion; }
		public function getFechaInicio() { return $this->fecha_inicio; }
		public function getFechaFin() { return $this->fecha_fin; }
		public function getDescuentoPorcentaje() { return $this->descuento_porcentaje; }
		public function getProductos() { return $this->productos; }

		public function setId($id) { $this->id = $id; }
		public function setNombre($nombre) { $this->nombre = $nombre; }
		public function setDescripcion($descripcion) { $this->descripcion = $descripcion; }
		public function setFechaInicio($fecha_inicio) { $this->fecha_inicio = $fecha_inicio; }
		public function setFechaFin($fecha_fin) { $this->fecha_fin = $fecha_fin; }
		public function setDescuentoPorcentaje($descuento_porcentaje) { $this->descuento_porcentaje = $descuento_porcentaje; }
		public function setProductos($productos) { $this->productos = $productos; }
		
		// Método de ayuda para añadir un producto al pack de la oferta
		public function addProductoRequerido($id_producto, $cantidad) {
			$this->productos[] = [
				'producto_id' => $id_producto,
				'cantidad' => $cantidad
			];
		}
	}
?>


<?php

require_once __DIR__ . "/PedidoServiceApp.php";
require_once __DIR__ . "/PedidoDTO.php";

class PedidoController {

    private static $instance = null;
    private $service;

    private function __construct() {
        $this->service = new PedidoServiceApp();
    }

    public static function getInstance() {

        if (self::$instance === null) {
            self::$instance = new PedidoController();
        }

        return self::$instance;
    }

    public function crearPedido($clienteId, $tipo, $productos) {

        $pedido = new PedidoDTO();

        $pedido->setClienteId($clienteId);
        $pedido->setTipo($tipo);
        $pedido->setProductos($productos);

        return $this->service->crearPedido($pedido);
    }

    public function verPedido($idPedido) {
        return $this->service->obtenerPedido($idPedido);
    }

}
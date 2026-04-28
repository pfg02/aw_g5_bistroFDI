<?php

require_once __DIR__ . "/PedidoServiceApp.php";
require_once __DIR__ . "/PedidoDTO.php";

class PedidoController
{
    private static $instance = null;
    private $service;

    private function __construct()
    {
        $this->service = new PedidoServiceApp();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new PedidoController();
        }

        return self::$instance;
    }

    public function crearPedido($clienteId, $tipo, $productos)
    {
        $pedido = new PedidoDTO();
        $pedido->setClienteId($clienteId);
        $pedido->setTipo($tipo);
        $pedido->setProductos($productos);

        return $this->service->crearPedido($pedido);
    }

    public function verPedido($idPedido)
    {
        return $this->service->obtenerPedido($idPedido);
    }

    public function actualizarEstadoPedido($idPedido, $nuevoEstado)
    {
        return $this->service->actualizarEstado($idPedido, $nuevoEstado);
    }

    public function asignarCocineroAPedido($idPedido, $idCocinero, $nuevoEstado = 'Cocinando')
    {
        return $this->service->asignarCocinero($idPedido, $idCocinero, $nuevoEstado);
    }

    public function obtenerPedidoActivoDeCocinero($idCocinero)
    {
        return $this->service->obtenerPedidoActivoDeCocinero($idCocinero);
    }

    public function verPedidosCliente($idCliente)
    {
        return $this->service->obtenerPedidosPorCliente($idCliente);
    }

    public function verPedidosActivos()
    {
        return $this->service->obtenerPedidosActivos();
    }

    public function obtenerProductosDePedido($idPedido)
    {
        return $this->service->obtenerProductosDePedido($idPedido);
    }

    public function verPedidosPorEstado($estado)
    {
        return $this->service->verPedidosPorEstado($estado);
    }

    public function eliminarPedido($idPedido)
    {
        return $this->service->eliminarPedido($idPedido);
    }

    public function marcarProductoComoPreparado($idPedido, $idProducto)
    {
        return $this->service->marcarProductoComoPreparado($idPedido, $idProducto);
    }

    public function todosProductosCocinaPreparados($idPedido)
    {
        return $this->service->todosProductosCocinaPreparados($idPedido);
    }

    public function marcarProductoServidoSala($idPedido, $idProducto)
    {
        return $this->service->marcarProductoServidoSala($idPedido, $idProducto);
    }
}
?>
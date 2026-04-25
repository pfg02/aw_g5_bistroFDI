<?php

require_once __DIR__ . '/../integracion/PedidoDAO.php';
require_once __DIR__ . '/../integracion/ProductoDAO.php';
require_once __DIR__ . '/../core/config.php';

class PedidoServiceApp
{
    private $pedidoDAO;
    private $productoDAO;

    public function __construct(?mysqli $db = null)
    {
        $db = $db ?? Application::getInstance()->conexionBd();

        $this->pedidoDAO = new PedidoDAO($db);
        $this->productoDAO = new ProductoDAO($db);
    }

    public function crearPedido($pedidoDTO)
    {
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

        $pedidoDTO->setTotal($total);
        $pedidoDTO->setEstado('Recibido');
        $pedidoDTO->setFecha(date('Y-m-d H:i:s'));

        return $this->pedidoDAO->guardarPedido($pedidoDTO);
    }

    public function obtenerPedido($idPedido)
    {
        return $this->pedidoDAO->buscarPedido($idPedido);
    }

    public function actualizarEstado($idPedido, $nuevoEstado)
    {
        return $this->pedidoDAO->actualizarEstado($idPedido, $nuevoEstado);
    }

    public function asignarCocinero($idPedido, $idCocinero, $nuevoEstado = 'Cocinando')
    {
        return $this->pedidoDAO->asignarCocinero($idPedido, $idCocinero, $nuevoEstado);
    }

    public function obtenerPedidoActivoDeCocinero($idCocinero)
    {
        return $this->pedidoDAO->obtenerPedidoActivoDeCocinero($idCocinero);
    }

    public function obtenerPedidosPorCliente($idCliente)
    {
        return $this->pedidoDAO->obtenerPedidosPorCliente($idCliente);
    }

    public function obtenerPedidosActivos()
    {
        return $this->pedidoDAO->obtenerPedidosActivos();
    }

    public function obtenerProductosDePedido($idPedido)
    {
        return $this->pedidoDAO->obtenerProductosDePedido($idPedido);
    }

    public function verPedidosPorEstado($estado)
    {
        return $this->pedidoDAO->verPedidosPorEstado($estado);
    }

    public function eliminarPedido($idPedido)
    {
        return $this->pedidoDAO->eliminarPedido($idPedido);
    }

    public function marcarProductoComoPreparado($idPedido, $idProducto)
    {
        return $this->pedidoDAO->marcarProductoComoPreparado($idPedido, $idProducto);
    }
}
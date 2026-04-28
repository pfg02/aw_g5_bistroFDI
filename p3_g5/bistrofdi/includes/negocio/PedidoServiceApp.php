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
        return $this->pedidoDAO->buscarPedido((int) $idPedido);
    }

    public function actualizarEstado($idPedido, $nuevoEstado)
    {
        return $this->pedidoDAO->actualizarEstado((int) $idPedido, (string) $nuevoEstado);
    }

    public function asignarCocinero($idPedido, $idCocinero, $nuevoEstado = 'Cocinando')
    {
        $idCocineroFinal = $idCocinero !== null ? (int) $idCocinero : null;

        return $this->pedidoDAO->asignarCocinero(
            (int) $idPedido,
            $idCocineroFinal,
            (string) $nuevoEstado
        );
    }

    public function obtenerPedidoActivoDeCocinero($idCocinero)
    {
        return $this->pedidoDAO->obtenerPedidoActivoDeCocinero((int) $idCocinero);
    }

    public function obtenerPedidosPorCliente($idCliente)
    {
        return $this->pedidoDAO->obtenerPedidosPorCliente((int) $idCliente);
    }

    public function obtenerPedidosActivos()
    {
        return $this->pedidoDAO->obtenerPedidosActivos();
    }

    public function obtenerProductosDePedido($idPedido)
    {
        return $this->pedidoDAO->obtenerProductosDePedido((int) $idPedido);
    }

    public function verPedidosPorEstado($estado)
    {
        return $this->pedidoDAO->verPedidosPorEstado((string) $estado);
    }

    public function eliminarPedido($idPedido)
    {
        return $this->pedidoDAO->eliminarPedido((int) $idPedido);
    }

    public function marcarProductoComoPreparado($idPedido, $idProducto)
    {
        return $this->pedidoDAO->marcarProductoComoPreparado(
            (int) $idPedido,
            (int) $idProducto
        );
    }

    public function todosProductosCocinaPreparados($idPedido)
    {
        return $this->pedidoDAO->todosProductosCocinaPreparados((int) $idPedido);
    }

    public function marcarProductoServidoSala($idPedido, $idProducto)
    {
        return $this->pedidoDAO->marcarProductoServidoSala(
            (int) $idPedido,
            (int) $idProducto
        );
    }
}
?>
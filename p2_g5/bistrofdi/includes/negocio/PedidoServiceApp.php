<?php

require_once __DIR__ . '/../integracion/PedidoDAO.php';
require_once __DIR__ . '/../config.php';

class PedidoServiceApp
{
    private PedidoDAO $pedidoDAO;

    public function __construct()
    {
        $this->pedidoDAO = new PedidoDAO();
    }

    public function crearPedido(int $clienteId, string $tipo, array $productos): int
    {
        if (empty($productos)) {
            throw new Exception('El carrito está vacío.');
        }

        $conn = obtenerConexionBD();
        $ids = array_map('intval', array_keys($productos));
        $idsStr = implode(',', $ids);

        $total = 0;
        $sql = "SELECT id, precio_base, iva FROM productos WHERE id IN ($idsStr)";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
            $cantidad = (int)$productos[$row['id']];
            $precioConIva = (float)$row['precio_base'] * (1 + ((float)$row['iva'] / 100));
            $total += $precioConIva * $cantidad;
        }

        $pedido = new PedidoDTO();
        $pedido->setClienteId($clienteId);
        $pedido->setTipo($tipo);
        $pedido->setProductos($productos);
        $pedido->setTotal($total);
        $pedido->setEstado('Recibido');
        $pedido->setFecha(date('Y-m-d H:i:s'));

        return $this->pedidoDAO->guardarPedido($pedido);
    }

    public function obtenerPedido(int $idPedido): ?array
    {
        return $this->pedidoDAO->buscarPedido($idPedido);
    }

    public function obtenerLineasPedido(int $idPedido): array
    {
        return $this->pedidoDAO->obtenerLineasPedido($idPedido);
    }

    public function obtenerPedidosPorCliente(int $idCliente): array
    {
        return $this->pedidoDAO->obtenerPedidosPorCliente($idCliente);
    }

    public function obtenerPedidosPorEstado(string $estado): array
    {
        return $this->pedidoDAO->obtenerPedidosPorEstado($estado);
    }

    public function pagarConTarjeta(int $idPedido, int $clienteId): bool
    {
        $pedido = $this->pedidoDAO->buscarPedido($idPedido);
        if (!$pedido || (int)$pedido['cliente_id'] !== $clienteId || $pedido['estado'] !== 'Recibido') {
            return false;
        }

        return $this->pedidoDAO->actualizarEstado($idPedido, 'En preparación');
    }

    public function cancelarPedidoCliente(int $idPedido, int $clienteId): bool
    {
        return $this->pedidoDAO->borrarPedidoCliente($idPedido, $clienteId);
    }

    public function accionCamarero(int $idPedido, string $accion): bool
    {
        $pedido = $this->pedidoDAO->buscarPedido($idPedido);
        if (!$pedido) {
            return false;
        }

        $estadoActual = $pedido['estado'];

        $mapa = [
            'cobrar'   => ['origen' => 'Recibido',     'destino' => 'En preparación'],
            'terminar' => ['origen' => 'Listo cocina', 'destino' => 'Terminado'],
            'entregar' => ['origen' => 'Terminado',    'destino' => 'Entregado'],
        ];

        if (!isset($mapa[$accion])) {
            return false;
        }

        if ($estadoActual !== $mapa[$accion]['origen']) {
            return false;
        }

        return $this->pedidoDAO->actualizarEstado($idPedido, $mapa[$accion]['destino']);
    }
}
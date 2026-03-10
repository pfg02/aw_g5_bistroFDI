<?php

/**
 * Clase de acceso a datos para pedidos.
 * @author Gabriel Omaña
 */

require_once __DIR__ . '/../negocio/PedidoDTO.php';
require_once __DIR__ . '/../../includes/config.php';

class PedidoDAO {

    /**
     * Guarda un nuevo pedido en la base de datos.
     * @param PedidoDTO $pedidoDTO El objeto con los datos del pedido a guardar.
     * @return int El ID del pedido recién creado.
     */
    public function guardarPedido($pedidoDTO) {

        $conn = obtenerConexionBD();

        $clienteId = $pedidoDTO->getClienteId();
        $tipo = $pedidoDTO->getTipo();
        $estado = $pedidoDTO->getEstado();
        $fecha = $pedidoDTO->getFecha();
        $total = $pedidoDTO->getTotal();

        $sql = "INSERT INTO pedidos (cliente_id, tipo, estado, fecha, total)
                VALUES ('$clienteId', '$tipo', '$estado', '$fecha', '$total')";

        $conn->query($sql);

        $pedidoId = $conn->insert_id;

        $productos = $pedidoDTO->getProductos();

        foreach ($productos as $productoId => $cantidad) {

            $sql = "INSERT INTO pedido_productos (pedido_id, producto_id, cantidad)
                    VALUES ('$pedidoId', '$productoId', '$cantidad')";

            $conn->query($sql);
        }

        return $pedidoId;
    }

    /**
     * Busca un pedido por su ID.
     * @param int $idPedido El ID del pedido a buscar.
     * @return array Un array asociativo con los datos del pedido, o null si no se encuentra.
     */
    public function buscarPedido($idPedido) {

        $conn = obtenerConexionBD();

        $sql = "SELECT * FROM pedidos WHERE id = '$idPedido'";

        $result = $conn->query($sql);

        return $result->fetch_assoc();
    }

}
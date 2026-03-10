

<?php

class PedidoDAO {

    public function guardarPedido($pedidoDTO) {

        global $db;

        $clienteId = $pedidoDTO->getClienteId();
        $tipo = $pedidoDTO->getTipo();
        $estado = $pedidoDTO->getEstado();
        $fecha = $pedidoDTO->getFecha();
        $total = $pedidoDTO->getTotal();

        $sql = "INSERT INTO pedidos (cliente_id, tipo, estado, fecha, total)
                VALUES ('$clienteId', '$tipo', '$estado', '$fecha', '$total')";

        $db->query($sql);

        $pedidoId = $db->insert_id;

        $productos = $pedidoDTO->getProductos();

        foreach ($productos as $productoId => $cantidad) {

            $sql = "INSERT INTO pedido_productos (pedido_id, producto_id, cantidad)
                    VALUES ('$pedidoId', '$productoId', '$cantidad')";

            $db->query($sql);
        }

        return $pedidoId;
    }

    public function buscarPedido($idPedido) {

        global $db;

        $sql = "SELECT * FROM pedidos WHERE id = '$idPedido'";

        $result = $db->query($sql);

        return $result->fetch_assoc();
    }

}
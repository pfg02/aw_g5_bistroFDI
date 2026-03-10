


<?php

require_once __DIR__ . "/../integration/PedidoDAO.php";

class PedidoServiceApp {

    private $dao;

    public function __construct() {
        $this->dao = new PedidoDAO();
    }

    public function crearPedido($pedidoDTO) {

        global $db;

        $productos = $pedidoDTO->getProductos();

        $ids = implode(",", array_keys($productos));

        $sql = "SELECT id, precio FROM productos WHERE id IN ($ids)";

        $result = $db->query($sql);

        $precios = [];

        while ($row = $result->fetch_assoc()) {
            $precios[$row["id"]] = $row["precio"];
        }

        $total = 0;

        foreach ($productos as $productoId => $cantidad) {
            $total += $precios[$productoId] * $cantidad;
        }

        $pedidoDTO->setTotal($total);
        $pedidoDTO->setEstado("RECIBIDO");
        $pedidoDTO->setFecha(date("Y-m-d H:i:s"));

        return $this->dao->guardarPedido($pedidoDTO);
    }

    public function obtenerPedido($idPedido) {
        return $this->dao->buscarPedido($idPedido);
    }

}
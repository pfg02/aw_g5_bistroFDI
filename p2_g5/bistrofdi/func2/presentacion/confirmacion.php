/**
 * Vista para confirmar que el pedido se ha creado correctamente.
 * @author Gabriel Omaña
 */

<?php

require_once "../business/PedidoController.php";

$controller = PedidoController::getInstance();

$idPedido = $_GET["id"];

$pedido = $controller->verPedido($idPedido);

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Pedido confirmado</title>
</head>

<body>

<h2>Pedido confirmado</h2>

<p>Número de pedido: <?php echo $pedido["id"]; ?></p>

<p>Estado: <?php echo $pedido["estado"]; ?></p>

<br>

<a href="pedido_inicio.php">Volver al inicio</a>

</body>
</html>
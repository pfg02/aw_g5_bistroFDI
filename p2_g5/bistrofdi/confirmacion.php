<?php

/**
	* Vista para mostrar la confirmación de un pedido.
	* @author Gabriel Omaña
	*/

require_once __DIR__ . '/includes/negocio/PedidoController.php';

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

<a href="pago.php?id=<?php echo htmlspecialchars($idPedido); ?>">Pago</a>

</body>
</html>
// Revision P2

<?php

/**
	* Controlador para añadir un producto al carrito de un pedido en curso.
	* @author Gabriel Omaña
	*/

session_start();

$productoId = $_POST["productoId"];
$cantidad = $_POST["cantidad"];

if (!isset($_SESSION["carrito"][$productoId])) {
	$_SESSION["carrito"][$productoId] = 0;
}

$_SESSION["carrito"][$productoId] += $cantidad;

header("Location: carrito.php");
exit();
// Revision P2

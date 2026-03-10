/**
 * Vista de confirmación para añadir un producto al carrito.
 * @author Gabriel Omaña
 */

<?php
session_start();

$productoId = $_POST["productoId"];
$cantidad = $_POST["cantidad"];

if (!isset($_SESSION["carrito"][$productoId])) {
    $_SESSION["carrito"][$productoId] = 0;
}

$_SESSION["carrito"][$productoId] += $cantidad;

header("Location: carrito.php");
exit();
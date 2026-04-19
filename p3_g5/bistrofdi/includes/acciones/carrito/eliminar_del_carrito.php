<?php
/**
 * Elimina un producto individual del carrito.
*/
	require_once __DIR__ . '/../../core/sesion.php';

	exigirLogin();
	exigirRol('cliente');

	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['productoId'])) {
		
		$id = (int)$_POST['productoId'];

		// Si el producto existe en el carrito, lo eliminamos
		if (isset($_SESSION['carrito'][$id])) {
			unset($_SESSION['carrito'][$id]);
			$_SESSION['mensaje_exito'] = "Artículo eliminado del carrito.";
		}
	}
	header("Location: carrito.php");
	exit();
?>
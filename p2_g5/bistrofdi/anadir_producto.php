<?php
/**
 * Añade un producto al carrito en la sesión y devuelve al catálogo.
 */

	require_once __DIR__ . '/includes/sesion.php';

	exigirLogin();
	exigirRol('cliente');

	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_producto'], $_POST['cantidad'])) {
		
		$id = (int)$_POST['id_producto'];
		$cantidad = (int)$_POST['cantidad'];

		if ($cantidad > 0) {
			// Si el carrito no existe, lo creamos vacío
			if (!isset($_SESSION['carrito'])) {
				$_SESSION['carrito'] = [];
			}

			// Si el producto ya está en el carrito, sumamos su cantidad, sino, lo creamos.
			if (isset($_SESSION['carrito'][$id])) {
				$_SESSION['carrito'][$id] += $cantidad;
			} else {
				$_SESSION['carrito'][$id] = $cantidad;
			}
			
			// Mensaje para que el usuario sepa que funcionó
			$_SESSION['mensaje_exito'] = "Artículo añadido al carrito.";
		}
	}

	header("Location: catalogo.php");
	exit();
?>
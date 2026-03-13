<?php
/**
 * Cancela el pedido en curso vaciando el carrito.
*/

	require_once __DIR__ . '/includes/sesion.php';

	exigirLogin();
	exigirRol('cliente');

	// Borramos el carrito de la sesión
	if (isset($_SESSION['carrito'])) {
		unset($_SESSION['carrito']);
	}

	// Borramos también el tipo de pedido (Local/Llevar) para que empiece de cero
	if (isset($_SESSION['tipoPedido'])) {
		unset($_SESSION['tipoPedido']);
	}

	// Mandamos un mensaje para avisar al usuario
	$_SESSION['mensaje_exito'] = "El carrito ha sido vaciado correctamente. Puedes empezar un nuevo pedido cuando quieras.";

	// Devolvemos a la página del catálogo
	header("Location: catalogo.php");
	exit();
?>
<?php

/**
 * Vista para iniciar un nuevo pedido.
 * @author Gabriel Omaña
 */

require_once __DIR__ . 'includes/sesion.php';

// exigimos inicio de sesión y rol de cliente para poder crear un pedido
exigirLogin();
exigirRol('cliente');

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Nuevo pedido</title>
</head>

<body>

<h2>Nuevo pedido</h2>

<form action="catalogo.php" method="POST">

<label for="tipo">Tipo de pedido:</label>

<select name="tipo" id="tipo">
    <option value="LOCAL">Consumir en local</option>
    <option value="LLEVAR">Para llevar</option>
</select>

<br><br>

<button type="submit">Empezar pedido</button>

</form>

</body>
</html>
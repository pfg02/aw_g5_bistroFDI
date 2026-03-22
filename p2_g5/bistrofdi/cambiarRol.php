<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/config.php'; 

exigirLogin();
exigirRol('gerente');

// 1. CAPTURAMOS EL ID DE LA URL (El usuario que quieres cambiar)
$idUsuario = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idUsuario <= 0) {
	header("Location: gestionarUsuarios.php");
	exit();
}

// 2. CONSULTA SQL: Buscamos al usuario según el ID de la URL
// IMPORTANTE: Aquí NO usamos $_SESSION, usamos $idUsuario
$sql = "SELECT id, nombre_usuario, rol FROM usuarios WHERE id = $idUsuario";
$resultado = $db->query($sql);
$usuarioAEditar = $resultado->fetch_assoc();

if (!$usuarioAEditar) {
	die("Error: El usuario que intentas editar no existe.");
}

// 3. Limpiamos el rol para la comparación
$rolActual = strtolower(trim($usuarioAEditar['rol'])); 

// 4. Procesar el cambio cuando se pulsa el botón
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$nuevoRol = $_POST['rol'];
	
	// Evitar que el gerente se quite el cargo a sí mismo
	if ($idUsuario == $_SESSION['id_usuario'] && $nuevoRol !== 'gerente') {
	$error = "No puedes quitarte el rol de gerente a ti mismo por seguridad.";
	} else {
	$stmt = $db->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
	$stmt->bind_param("si", $nuevoRol, $idUsuario);
	
	if ($stmt->execute()) {
	header("Location: gestionarUsuarios.php?mensaje=actualizado");
	exit();
	} else {
	$error = "Error al actualizar los datos.";
	}
	}
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<title>Cambiar Rol - Bistro FDI</title>
	<link rel="stylesheet" href="css/estilos.css">
</head>
<body class="body-inicio">
	<?php include __DIR__ . '/includes/nav.php'; ?>

	<main class="main-bienvenida">
	<section class="tarjeta-presentacion">
	
	<h2>Gestión de Permisos</h2>
	
	<p>Editando a: <strong><?php echo htmlspecialchars($usuarioAEditar['nombre_usuario']); ?></strong></p>
	
	<div class="divisor"></div>

	<?php if (isset($error)): ?>
	<p class="error-msg"><?php echo $error; ?></p>
	<?php endif; ?>

	<form method="POST" action="cambiarRol.php?id=<?php echo $idUsuario; ?>">
	
	<label for="rol">Seleccione el nuevo cargo:</label>
	
	<select name="rol" id="rol">
	<option value="cliente" <?php if($rolActual == 'cliente') echo 'selected'; ?>>Cliente</option>
	<option value="cocinero" <?php if($rolActual == 'cocinero') echo 'selected'; ?>>Cocinero</option>
	<option value="camarero" <?php if($rolActual == 'camarero') echo 'selected'; ?>>Camarero</option>
	<option value="gerente" <?php if($rolActual == 'gerente') echo 'selected'; ?>>Gerente</option>
	</select>

	<button type="submit" class="btn-login">Actualizar Rango</button>
	
	</form>

	<div class="contenedor-enlaces">
	<a href="gestionarUsuarios.php">Volver al panel de gestión</a>
	</div>

	</section>
	</main>

	<footer class="footer-simple">
	<p>&copy; <?php echo date('Y'); ?> Bistró FDI</p>
	</footer>
</body>
</html>
// Revision P2

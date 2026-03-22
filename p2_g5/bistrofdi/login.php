<?php
require_once __DIR__ . '/includes/sesion.php';

$mensajeError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$nombreUsuario = trim($_POST['nombre_usuario'] ?? '');
	$password      = $_POST['password'] ?? '';

	if (loginUsuario($nombreUsuario, $password)) {
	header('Location: index.php');
	exit;
	} else {
	$mensajeError = 'Usuario no encontrado o contraseña incorrecta.';
	}
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<title>Iniciar sesión - Bistro FDI</title>
	<link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<?php include __DIR__ . '/includes/vistas/comun/nav.php'; ?>
	<h1>Iniciar sesión</h1>

	<?php if ($mensajeError): ?>
	<p style="color:red;"><?php echo htmlspecialchars($mensajeError); ?></p>
	<?php endif; ?>

	<form method="post" action="login.php">
	<label>Nombre de usuario:
	<input type="text" name="nombre_usuario" required>
	</label>
	<br><br>
	<label>Contraseña:
	<input type="password" name="password" required>
	</label>
	<br><br>
	<button type="submit">Entrar</button>
	</form>

	<p><a href="index.php">Volver al inicio</a></p>
	<p><a href="registro.php">Registrarse</a></p>
	<p><a href="olvidoPassword.php">He olvidado mi contraseña</a></p>
</body>
</html>
// Revision P2

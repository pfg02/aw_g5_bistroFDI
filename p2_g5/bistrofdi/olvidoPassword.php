<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/funcionesUsuarios.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$identificador = trim($_POST['identificador'] ?? '');

	// Buscamos por nombre de usuario o email (muy simple)
	$usuario = null;

	if ($identificador !== '') {
	// Intentar como nombre de usuario
	$usuario = obtenerUsuarioPorNombre($identificador);

	// Si no, intentar como email
	if (!$usuario) {
	$conn = obtenerConexionBD();
	$sql = "SELECT * FROM usuarios WHERE email = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("s", $identificador);
	$stmt->execute();
	$res = $stmt->get_result();
	$usuario = $res->fetch_assoc();
	$stmt->close();
	$conn->close();
	}
	}

	// Para la práctica: no mandamos correo real, solo mostramos mensaje.
	if ($usuario) {
	$mensaje = "Si el usuario existe y el email es correcto, se enviará un correo con instrucciones para restablecer la contraseña.";
	} else {
	$mensaje = "Si el usuario/email existe en el sistema, se enviará un correo con instrucciones (por seguridad no indicamos si existe o no).";
	}
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<title>Olvidé mi contraseña - Bistro FDI</title>
	<link rel="stylesheet" href="css/estilos.css">
</head>
<body>
	<?php include __DIR__ . '/includes/nav.php'; ?>

	<h1>Recuperar contraseña</h1>

	<?php if ($mensaje): ?>
	<p><?php echo htmlspecialchars($mensaje); ?></p>
	<?php endif; ?>

	<form method="post" action="olvidoPassword.php">
	<label>Nombre de usuario o email:
	<input type="text" name="identificador" required>
	</label>
	<br><br>
	<button type="submit">Enviar instrucciones</button>
	</form>

	<p><a href="login.php">Volver a iniciar sesión</a></p>
	<p><a href="index.php">Volver al inicio</a></p>
</body>
</html>
// Revision P2

<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/funcionesUsuarios.php';

exigirLogin();

$idUsuario = $_SESSION['id_usuario'];
$usuario   = obtenerUsuarioPorId($idUsuario);

$mensajeOk = '';
$mensajeError = '';

$carpetaAvatares = 'img/avatares';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	// Opción A: avatar predefinido
	if (isset($_POST['avatar_predefinido'])) {
		$archivo = basename($_POST['avatar_predefinido']); // seguridad básica
		$ruta = $carpetaAvatares . '/' . $archivo;
	if (actualizarAvatar($idUsuario, $ruta)) {
		$_SESSION['avatar'] = $ruta; // ACTUALIZAMOS SESIÓN PARA EL NAV
		$mensajeOk = 'Avatar actualizado correctamente.';
	} else {
	$mensajeError = 'Error al actualizar el avatar.';
	}

	// Opción B: subir imagen propia
	} elseif (isset($_POST['accion']) && $_POST['accion'] === 'subir') {
	if (!empty($_FILES['archivo']['name'])) {
	$nombreArchivo = basename($_FILES['archivo']['name']);
	$destino       = $carpetaAvatares . '/' . $nombreArchivo;

	if (move_uploaded_file($_FILES['archivo']['tmp_name'], $destino)) {
	if (actualizarAvatar($idUsuario, $destino)) {
	$_SESSION['avatar'] = $destino; // ACTUALIZAMOS SESIÓN PARA EL NAV
	$mensajeOk = 'Avatar subido y actualizado correctamente.';
	} else {
	$mensajeError = 'Error al actualizar el avatar en la BD.';
	}
	} else {
	$mensajeError = 'Error al subir la imagen.';
	}
	} else {
	$mensajeError = 'No has seleccionado ninguna imagen.';
	}

	// Opción C: avatar por defecto
	} elseif (isset($_POST['accion']) && $_POST['accion'] === 'defecto') {
	$ruta = $carpetaAvatares . '/default.png';
	if (actualizarAvatar($idUsuario, $ruta)) {
	$_SESSION['avatar'] = $ruta; // ACTUALIZAMOS SESIÓN PARA EL NAV
	$mensajeOk = 'Avatar por defecto restaurado.';
	} else {
	$mensajeError = 'Error al actualizar el avatar por defecto.';
	}
	}

	$usuario = obtenerUsuarioPorId($idUsuario);
}

$avataresPredefinidos = [
	'camarero.png',
	'cliente.png',
	'cocinero.png',
	'gerente.png',
	'default.png'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<title>Cambiar avatar - Bistro FDI</title>
	<link rel="stylesheet" href="css/estilos.css">
</head>
<body>
	<?php include __DIR__ . '/includes/nav.php'; ?>

	<div class="contenedor-principal">
	<h1>Cambiar avatar</h1>

	<?php if ($mensajeError): ?>
	<p style="color:red;"><?php echo htmlspecialchars($mensajeError); ?></p>
	<?php endif; ?>
	<?php if ($mensajeOk): ?>
	<p style="color:green;"><?php echo htmlspecialchars($mensajeOk); ?></p>
	<?php endif; ?>

	<p>Avatar actual:</p>
	<img src="<?php echo htmlspecialchars($usuario['avatar']); ?>" alt="Avatar actual" width="120">

	<hr>

	<h2>Seleccionar avatar predefinido</h2>
	<form method="post">
	<?php foreach ($avataresPredefinidos as $archivo): ?>
	<label style="display:inline-block; margin-right:10px;">
	<input type="radio" name="avatar_predefinido" value="<?php echo htmlspecialchars($archivo); ?>">
	<img src="img/avatares/<?php echo htmlspecialchars($archivo); ?>" width="80" alt="">
	</label>
	<?php endforeach; ?>
	<br><br>
	<button type="submit">Guardar avatar predefinido</button>
	</form>

	<hr>

	<h2>Subir imagen propia</h2>
	<form method="post" enctype="multipart/form-data">
	<input type="hidden" name="accion" value="subir">
	<input type="file" name="archivo" accept="image/*">
	<br><br>
	<button type="submit">Subir y usar esta imagen</button>
	</form>

	<hr>

	<h2>Usar avatar por defecto</h2>
	<form method="post">
	<input type="hidden" name="accion" value="defecto">
	<button type="submit">Restaurar avatar por defecto</button>
	</form>

	<br>
	<p><a href="perfil.php">Volver a mi perfil</a></p>
	</div>
</body>
</html>
// Revision P2

<?php
// 1. Carga de configuración y sesión
require_once 'includes/config.php';
require_once 'includes/sesion.php';

// --- ESTA LÍNEA ES EL TRUCO FINAL ---
// Desactiva que mysqli lance Fatal Errors. Ahora el error se guardará en $db->errno
mysqli_report(MYSQLI_REPORT_OFF);

// 2. Seguridad: Solo el gerente entra aquí
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'gerente') {
	header("Location: index.php");
	exit();
}

// 3. Lógica para insertar la categoría
$mensaje = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$nombre = trim($_POST['nombre']);
	$descripcion = $_POST['descripcion'];
	$nombre_imagen = "default.jpg"; 

	// Manejo de la subida de imagen
	if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
	$ruta_destino = "img/categorias/";
	$nombre_archivo = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES['imagen']['name']); 
	
	if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino . $nombre_archivo)) {
	$nombre_imagen = $nombre_archivo;
	}
	}

	// 4. Inserción manual sin try-catch (usando el código de error directo)
	$stmt = $db->prepare("INSERT INTO categorias (nombre, descripcion, imagen) VALUES (?, ?, ?)");
	$stmt->bind_param("sss", $nombre, $descripcion, $nombre_imagen);

	if ($stmt->execute()) {
	// ÉXITO: Redirigimos
	header("Location: gestion_categorias.php?msg=ok_creado");
	exit();
	} else {
	// ERROR: Comprobamos si el código de error es 1062 (Duplicado)
	if ($db->errno == 1062) {
	$mensaje = "La categoría '<strong>" . htmlspecialchars($nombre) . "</strong>' ya existe. Por favor, usa un nombre diferente.";
	} else {
	$mensaje = "Error en la base de datos: " . $db->error;
	}
	}
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<title>Añadir Nueva Categoría - Bistró FDI</title>
	<link rel="stylesheet" href="css/estilos.css">
</head>
<body class="body-gestion">

	<?php include 'includes/nav.php'; ?>

	<main class="main-content">
	<header class="header-gestion">
	<h1>Nueva Categoría</h1>
	</header>

	<section class="contenedor-tabla-profesional">
	
	<?php if ($mensaje): ?>
	<div class="alerta-error">
	<?php echo $mensaje; ?>
	</div>
	<?php endif; ?>

	<form action="nueva_categoria.php" method="POST" enctype="multipart/form-data" class="form-profesional">
	
	<div class="campo">
	<label>Nombre de la Categoría:</label>
	<input type="text" name="nombre" placeholder="Ej: Postres, Bebidas..." 
	required value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">
	</div>

	<div class="campo">
	<label>Descripción:</label>
	<textarea name="descripcion" placeholder="Breve descripción..."><?php echo isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : ''; ?></textarea>
	</div>

	<div class="campo">
	<label>Imagen:</label>
	<input type="file" name="imagen" accept="image/*">
	</div>

	<div class="botones-form">
	<button type="submit" class="btn-anadir">Crear Categoría</button>
	<a href="gestion_categorias.php" class="btn-borrar" style="text-decoration: none;">Cancelar</a>
	</div>
	</form>
	</section>
	</main>

</body>
</html>
<?php
// 1. Carga de configuración y sesión
require_once 'includes/config.php';
require_once 'includes/sesion.php';

// 2. Seguridad: Solo el gerente puede gestionar
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'gerente') {
	header("Location: index.php");
	exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<title>Gestión de Categorías - Bistró FDI</title>
	<link rel="stylesheet" href="css/estilos.css?v=1.2">
</head>
<body class="body-gestion">

	<?php include 'includes/nav.php'; ?>

	<main class="main-content" style="padding: 20px; max-width: 1200px; margin: 0 auto;">
	<header class="header-gestion" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
	<h1>Gestión de Categorías</h1>
	<a href="nueva_categoria.php" class="btn-anadir" style="background: #28a745; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold;">+ Añadir Categoría</a>
	</header>

	<section class="contenedor-tabla-profesional" style="background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden;">
	<table class="tabla-gestion" style="width: 100%; border-collapse: collapse; table-layout: fixed;">
	<thead>
	<tr style="background: #343a40; color: white;">
	<th style="width: 100px; padding: 15px; text-align: center;">Icono</th>
	<th style="width: 200px; padding: 15px; text-align: left;">Nombre</th>
	<th style="padding: 15px; text-align: left;">Descripción</th>
	<th style="width: 180px; padding: 15px; text-align: center;">Acciones</th>
	</tr>
	</thead>
	<tbody>
	<?php
	$sql = "SELECT * FROM categorias ORDER BY nombre ASC";
	$resultado = $db->query($sql);

	if ($resultado && $resultado->num_rows > 0):
	while ($cat = $resultado->fetch_assoc()):
	
	$nombre_foto = $cat['imagen'];
	$ruta_mostrar = "img/categorias/" . $nombre_foto;
	
	// Verificación física del archivo para evitar imágenes rotas
	$foto_valida = (!empty($nombre_foto) && file_exists(__DIR__ . "/" . $ruta_mostrar));
	?>
	<tr style="border-bottom: 1px solid #eee;">
	<td style="padding: 10px; text-align: center; vertical-align: middle;">
	<div style="width: 70px; height: 50px; margin: 0 auto; overflow: hidden; border-radius: 4px; border: 1px solid #ddd; background: #f8f9fa;">
	<?php if ($foto_valida): ?>
	<img src="<?php echo $ruta_mostrar; ?>" 
	alt="<?php echo htmlspecialchars($cat['nombre']); ?>" 
	style="width: 100%; height: 100%; object-fit: cover; display: block;">
	<?php else: ?>
	<div style="font-size: 9px; color: #999; line-height: 50px; text-transform: uppercase;">Sin icono</div>
	<?php endif; ?>
	</div>
	</td>

	<td style="padding: 15px; vertical-align: middle;">
	<strong style="color: #333;"><?php echo htmlspecialchars($cat['nombre']); ?></strong>
	</td>

	<td style="padding: 15px; vertical-align: middle; color: #666; font-size: 0.9em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
	<?php echo htmlspecialchars($cat['descripcion']); ?>
	</td>

	<td style="padding: 15px; text-align: center; vertical-align: middle;">
	<div style="display: flex; gap: 8px; justify-content: center;">
	<a href="editar_categoria.php?id=<?php echo $cat['id']; ?>" class="btn-editar" style="color: #007bff; text-decoration: none; font-weight: bold; font-size: 0.85em;">Editar</a>
	<a href="eliminar_categoria.php?id=<?php echo $cat['id']; ?>" 
	class="btn-borrar" 
	style="color: #dc3545; text-decoration: none; font-weight: bold; font-size: 0.85em;"
	onclick="return confirm('¿Seguro que deseas eliminar esta categoría?')">Eliminar</a>
	</div>
	</td>
	</tr>
	<?php 
	endwhile; 
	else: 
	?>
	<tr>
	<td colspan="4" style="text-align:center; padding: 50px; color: #999;">
	No hay categorías registradas en la base de datos.
	</td>
	</tr>
	<?php endif; ?>
	</tbody>
	</table>
	</section>
	</main>

	<footer style="text-align: center; padding: 20px; color: #888; font-size: 0.8em;">
	&copy; <?php echo date('Y'); ?> Bistró FDI - Panel de Control
	</footer>

</body>
</html>
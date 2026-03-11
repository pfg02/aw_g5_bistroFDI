<?php
session_start();

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/producto.php';

if (!isset($_SESSION['rol']) || !tienePermiso('gerente')) {
	header("Location: login.php");
	exit();
}

$query = "
	SELECT p.*, c.nombre AS categoria_nombre
	FROM productos p
	LEFT JOIN categorias c ON p.id_categoria = c.id
";

$resultado = $db->query($query);

if (!$resultado) {
	die("Error en la consulta: " . $db->error);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<title>Gestión de Productos</title>
	<link rel="stylesheet" href="css/estilos.css">
</head>
<body>

<?php include __DIR__ . '/includes/nav.php'; ?>

<div class="container">
	<h1>Gestión de Productos</h1>

	<?php if (isset($_GET['msg']) && $_GET['msg'] === 'eliminado'): ?>
	<div style="background:#d4edda;color:#155724;padding:10px;border-radius:5px;margin-bottom:20px;">
	Producto eliminado correctamente.
	</div>
	<?php endif; ?>

	<?php if (isset($_GET['msg']) && $_GET['msg'] === 'error_fk'): ?>
	<div style="background:#f8d7da;color:#721c24;padding:10px;border-radius:5px;margin-bottom:20px;">
	No se puede eliminar porque el producto está asociado a pedidos.
	</div>
	<?php endif; ?>

	<a href="editar_producto.php" class="btn btn-primary" style="margin-bottom:20px; display:inline-block;">
	+ Añadir Nuevo Producto
	</a>

	<table border="1" cellpadding="8" cellspacing="0" width="100%">
	<thead>
	<tr>
	<th>Nombre</th>
	<th>Descripción</th>
	<th>Categoría</th>
	<th>Imágenes</th>
	<th>Precio Base</th>
	<th>IVA</th>
	<th>Precio Final</th>
	<th>Stock</th>
	<th>Estado</th>
	<th>Acciones</th>
	</tr>
	</thead>
	<tbody>
	<?php while ($row = $resultado->fetch_assoc()): ?>

	<?php
	// Detectar el campo ID real
	$idProducto = 0;
	if (isset($row['id_producto'])) {
	$idProducto = (int)$row['id_producto'];
	} elseif (isset($row['id'])) {
	$idProducto = (int)$row['id'];
	}

	$imagenes = !empty($row['imagen']) ? explode(',', $row['imagen']) : [];

	$p = new Producto(
	$idProducto,
	$row['id_categoria'] ?? 0,
	$row['nombre'] ?? '',
	$row['descripcion'] ?? '',
	$row['precio_base'] ?? 0,
	$row['iva'] ?? 0,
	$row['stock'] ?? 0,
	$row['ofertado'] ?? 0,
	$imagenes
	);
	?>

	<tr>
	<td><strong><?= htmlspecialchars($row['nombre'] ?? '') ?></strong></td>
	<td><?= htmlspecialchars($row['descripcion'] ?? '---') ?></td>
	<td><?= htmlspecialchars($row['categoria_nombre'] ?? 'General') ?></td>

	<td>
	<div style="display:flex; gap:5px; flex-wrap:wrap;">
	<?php if (!empty($imagenes)): ?>
	<?php foreach ($imagenes as $img): ?>
	<?php $ruta = 'img/productos/' . trim($img); ?>
	<img
	src="<?= htmlspecialchars($ruta) ?>"
	alt="Imagen producto"
	style="width:40px;height:40px;object-fit:cover;border:1px solid #ccc;border-radius:4px;"
	onerror="this.src='https://via.placeholder.com/40'"
	>
	<?php endforeach; ?>
	<?php else: ?>
	<span style="color:gray;">Sin fotos</span>
	<?php endif; ?>
	</div>
	</td>

	<td><?= number_format((float)($row['precio_base'] ?? 0), 2) ?> €</td>
	<td><?= (int)($row['iva'] ?? 0) ?> %</td>
	<td><strong><?= number_format((float)$p->getPrecioFinal(), 2) ?> €</strong></td>
	<td><?= (int)($row['stock'] ?? 0) ?></td>

	<td>
	<?php if ((int)($row['ofertado'] ?? 0) === 1): ?>
	<span style="color:green;font-weight:bold;">EN CARTA</span>
	<?php else: ?>
	<span style="color:red;font-weight:bold;">RETIRADO</span>
	<?php endif; ?>
	</td>

	<td>
	<a href="editar_producto.php?id=<?= $idProducto ?>">Editar</a> |

	<a href="borrar_producto.php?id=<?= $idProducto ?>"
	onclick="return confirm('¿Retirar de la carta?')">
	Baja
	</a> |

	<a href="eliminar_producto.php?id=<?= $idProducto ?>"
	onclick="return confirm('¿Eliminar definitivamente este producto?')"
	style="color:red;">
	Eliminar
	</a>
	</td>
	</tr>

	<?php endwhile; ?>
	</tbody>
	</table>
</div>

</body>
</html>
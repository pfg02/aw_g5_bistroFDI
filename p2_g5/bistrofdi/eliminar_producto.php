<?php
session_start();

require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['rol']) || !tienePermiso('gerente')) {
    header("Location: login.php");
    exit();
}

$id = 0;

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
} elseif (isset($_GET['id_producto'])) {
    $id = (int)$_GET['id_producto'];
}

if ($id <= 0) {
    die("ID de producto no válido.");
}

// Ajusta aquí si tu clave primaria no es 'id'
$campoId = "id";

// Si en tu BD el campo es id_producto, cambia la línea anterior por:
// $campoId = "id_producto";

$sql = "
    SELECT p.*, c.nombre AS categoria_nombre
    FROM productos p
    LEFT JOIN categorias c ON p.id_categoria = c.id
    WHERE p.$campoId = ?
";

$stmt = $db->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$producto = $resultado->fetch_assoc();
$stmt->close();

if (!$producto) {
    die("No se encontró el producto con ID " . $id);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idPost = 0;

    if (isset($_POST['id'])) {
        $idPost = (int)$_POST['id'];
    } elseif (isset($_POST['id_producto'])) {
        $idPost = (int)$_POST['id_producto'];
    }

    if ($idPost <= 0) {
        die("ID de producto no válido.");
    }

    $stmtDelete = $db->prepare("DELETE FROM productos WHERE $campoId = ?");
    $stmtDelete->bind_param("i", $idPost);

    if ($stmtDelete->execute()) {
        $stmtDelete->close();
        header("Location: gestion_productos.php?msg=eliminado");
        exit();
    } else {
        $error = $stmtDelete->error;
        $errno = $db->errno;
        $stmtDelete->close();

        if ($errno == 1451 || stripos($error, 'foreign key') !== false) {
            header("Location: gestion_productos.php?msg=error_fk");
            exit();
        }

        die("Error al eliminar el producto: " . htmlspecialchars($error));
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Eliminar producto</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>

<?php include __DIR__ . '/includes/nav.php'; ?>

<div class="container" style="max-width: 900px; margin: 30px auto;">
    <h1>Eliminar producto</h1>

    <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #ffeeba;">
        Estás a punto de eliminar este producto definitivamente de la base de datos.
    </div>

    <div style="background: #f8f9fa; border: 1px solid #ddd; border-radius: 6px; padding: 20px; margin-bottom: 20px;">
        <p><strong>Nombre:</strong> <?= htmlspecialchars($producto['nombre'] ?? '') ?></p>
        <p><strong>Descripción:</strong> <?= htmlspecialchars($producto['descripcion'] ?? '---') ?></p>
        <p><strong>Categoría:</strong> <?= htmlspecialchars($producto['categoria_nombre'] ?? 'General') ?></p>
        <p><strong>Precio base:</strong> <?= number_format((float)($producto['precio_base'] ?? 0), 2) ?> €</p>
        <p><strong>IVA:</strong> <?= (int)($producto['iva'] ?? 0) ?> %</p>
        <p><strong>Stock:</strong> <?= (int)($producto['stock'] ?? 0) ?></p>
    </div>

    <form method="POST">
        <input type="hidden" name="id" value="<?= $id ?>">

        <button type="submit" style="background: #dc3545; color: white; border: none; padding: 10px 18px; border-radius: 5px; cursor: pointer;">
            Sí, eliminar definitivamente
        </button>

        <a href="gestion_productos.php" style="margin-left: 10px; text-decoration: none; background: #6c757d; color: white; padding: 10px 18px; border-radius: 5px;">
            Cancelar
        </a>
    </form>
</div>

</body>
</html>
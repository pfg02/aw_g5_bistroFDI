<?php
session_start();
require_once 'config.php';
require_once 'Producto.php';

// --- LOGIN FALSO (BORRAR CUANDO TU COMPAÑERO TERMINE LA FUNC 0) ---
$_SESSION['rol'] = 'gerente'; 
// ----------------------------------------------------------------

// Seguridad: Solo Gerentes pueden entrar aquí
if (!tienePermiso('gerente')) {
    die("Acceso denegado. Se requiere rol de Gerente.");
}

$conexion = new mysqli($db_host, $db_user, $db_pass, $db_name);
$query = "SELECT * FROM productos"; 
$resultado = $conexion->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestión de Productos - Bistró FDI</title>
</head>
<body>
    <h1>Panel de Gestión de Productos</h1>
    <table border="1">
        <tr>
            <th>Nombre</th>
            <th>Precio Base</th>
            <th>IVA</th>
            <th>Precio Final</th>
            <th>Stock</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
        <?php while($row = $resultado->fetch_assoc()): 
            $p = new Producto($row['id'], $row['id_categoria'], $row['nombre'], $row['descripcion'], $row['precio_base'], $row['iva'], $row['stock'], $row['ofertado']);
        ?>
        <tr>
            <td><?= $p->nombre ?></td>
            <td><?= $p->precio_base ?>€</td>
            <td><?= $p->iva ?>%</td>
            <td><strong><?= number_format($p->getPrecioFinal(), 2) ?>€</strong></td>
            <td><?= $p->stock ?></td>
            <td><?= $p->ofertado ? 'Ofertado' : 'Baja Lógica' ?></td>
            <td>
                <a href="editar.php?id=<?= $p->id ?>">Editar</a> | 
                <a href="borrar_logico.php?id=<?= $p->id ?>">Dar de baja</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
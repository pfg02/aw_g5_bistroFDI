<?php
/**
 * Vista del carrito de un pedido en curso.
 * @author Gabriel Omaña
 */

require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/config.php';

// Seguridad
exigirLogin();
exigirRol('cliente');

$carrito = $_SESSION["carrito"] ?? [];
$tipoPedido = $_SESSION["tipoPedido"] ?? 'No definido';

$productos = [];
$total = 0;

if (!empty($carrito)) {
    // array_map('intval', ...) es un truco de seguridad para asegurar que solo haya números en los IDs
    $ids = implode(",", array_map('intval', array_keys($carrito)));

    $conn = obtenerConexionBD();
    // ¡Añadimos el iva a la consulta para mostrar el precio final!
    $sql = "SELECT id, nombre, precio_base, iva FROM productos WHERE id IN ($ids)";
    $result = $conn->query($sql);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $productos[$row["id"]] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Carrito - Bistró FDI</title>
    <link rel="stylesheet" href="css/estilos.css?v=2.0">
</head>
<body class="body-inicio">

    <?php include __DIR__ . '/includes/nav.php'; ?>

    <main class="main-bienvenida">
        <section class="tarjeta-presentacion tarjeta-ancha">
            
            <h1>Mi <span>Carrito</span></h1>
            <p class="lema">Revisa tu pedido (<?php echo htmlspecialchars($tipoPedido); ?>)</p>
            <div class="divisor"></div>

            <div class="mensaje-sesion mensaje-sesion-ancho">
                
                <?php if (empty($carrito)): ?>
                    <p>Tu carrito está vacío ahora mismo.</p>
                    <div class="contenedor-botones-carrito">
                        <a href="catalogo.php" class="btn-login">Volver al Catálogo</a>
                    </div>
                
                <?php else: ?>
                    
                    <table class="tabla-pedidos">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Precio (con IVA)</th>
                                <th>Cantidad</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            foreach ($carrito as $productoId => $cantidad): 
                                // Por si el producto fue borrado de la BD mientras estaba en el carrito
                                if (!isset($productos[$productoId])) continue; 

                                $producto = $productos[$productoId];
                                $precioBase = $producto["precio_base"];
                                $porcentajeIva = $producto["iva"];
                                
                                // Cálculo matemático: Precio Base + (Precio base * 0.21, por ejemplo)
                                $precioConIva = $precioBase + ($precioBase * ($porcentajeIva / 100));
                                $subtotal = $precioConIva * $cantidad;
                                $total += $subtotal;
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($producto["nombre"]); ?></strong></td>
                                <td><?php echo number_format($precioConIva, 2); ?> €</td>
                                <td><?php echo $cantidad; ?></td>
                                <td><strong><?php echo number_format($subtotal, 2); ?> €</strong></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="resumen-carrito">
                        <h3>Total a Pagar:</h3>
                        <div class="total-destacado"><?php echo number_format($total, 2); ?> €</div>
                    </div>

                    <form action="confirmar_pedido.php" method="POST" class="form-confirmar">
                        <button type="submit" class="btn-confirmar-compra">
                            ✅ Confirmar y Procesar Pedido
                        </button>
                    </form>

                <?php endif; ?>

            </div>

            <?php if (!empty($carrito)): ?>
                <div class="contenedor-botones-carrito">
                    <a href="catalogo.php" class="btn-admin">Seguir comprando</a>
                    <a href="cancelar_pedido.php" class="btn-login btn-peligro">Vaciar Carrito</a>
                </div>
            <?php endif; ?>

        </section>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
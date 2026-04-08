<?php
	/**
	 * Interfaz de tablet para los cocineros.
	 */
	require_once __DIR__ . '/includes/sesion.php';
	require_once __DIR__ . '/includes/negocio/PedidoController.php';

	exigirLogin();
	exigirRol('cocinero', 'gerente', 'admin');

	$idCocinero = $_SESSION['id_usuario'];

	global $db;
	if (!isset($db)) {
		$db = obtenerConexionBD();
	}

	// 1. Obtener pedidos "En preparación" (Nuevos, sin asignar)
	$queryNuevos = "SELECT id, fecha, tipo_pedido FROM pedidos WHERE estado = 'En preparación' ORDER BY fecha ASC";
	$resNuevos = $db->query($queryNuevos);
	$pedidosNuevos = $resNuevos->fetch_all(MYSQLI_ASSOC);

	// 2. Obtener MI pedido activo (Cocinando)
	$queryMio = "SELECT id, tipo_pedido FROM pedidos WHERE estado = 'Cocinando' AND cocinero_id = ? LIMIT 1";
	$stmtMio = $db->prepare($queryMio);
	$stmtMio->bind_param("i", $idCocinero);
	$stmtMio->execute();
	$miPedido = $stmtMio->get_result()->fetch_assoc();

	// 3. Si tengo un pedido activo, sacamos sus platos
	$platos = [];
	$todosPreparados = false;
	if ($miPedido) {
		$qPlatos = "SELECT pp.producto_id, p.nombre, pp.cantidad, pp.preparado 
					FROM pedido_productos pp 
					JOIN productos p ON pp.producto_id = p.id 
					WHERE pp.pedido_id = ?";
		$stmtPlatos = $db->prepare($qPlatos);
		$stmtPlatos->bind_param("i", $miPedido['id']);
		$stmtPlatos->execute();
		$platos = $stmtPlatos->get_result()->fetch_all(MYSQLI_ASSOC);
		
		// Comprobamos si todos están a 1 (preparados)
		$todosPreparados = count($platos) > 0 && empty(array_filter($platos, fn($p) => $p['preparado'] == 0));
	}

	$tituloPagina = 'Bistró FDI - Cocina';
	$bodyClass    = 'f0-body';

	ob_start();
?>

<div class="pantalla-tablet-cocina" style="display: flex; height: 85vh; gap: 20px; padding: 20px; background: #f4f7f6;">
    
    <div style="flex: 1; background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow-y: auto;">
        <h2 style="color: #2c3e50; border-bottom: 3px solid #dc3545; padding-bottom: 10px;">📋 Comandas Nuevas</h2>
        
        <?php if (empty($pedidosNuevos)): ?>
            <p style="color: #888; font-style: italic; text-align: center; margin-top: 50px;">No hay comandas pendientes.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 15px; margin-top: 20px;">
                <?php foreach ($pedidosNuevos as $p): ?>
                    <div style="background: #fff3cd; border-left: 5px solid #ffc107; padding: 15px; border-radius: 6px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h3 style="margin: 0;">Ticket #<?= $p['id'] ?></h3>
                                <small><?= date('H:i', strtotime($p['fecha'])) ?> | <?= $p['tipo_pedido'] ?></small>
                            </div>
                            <form action="procesar_cocina.php" method="POST" style="margin: 0;">
                                <input type="hidden" name="accion" value="reclamar">
                                <input type="hidden" name="id_pedido" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn-admin" style="background: #28a745; padding: 15px; font-size: 1.1em;" <?= $miPedido ? 'disabled title="Termina tu pedido actual primero"' : '' ?>>
                                    👨‍🍳 Cocinar
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div style="flex: 2; background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 2px solid #17a2b8;">
        <h2 style="color: #17a2b8; border-bottom: 3px solid #17a2b8; padding-bottom: 10px;">🔥 Mi Mesa de Trabajo</h2>
        
        <?php if (!$miPedido): ?>
            <div style="text-align: center; margin-top: 100px; color: #6c757d;">
                <h1 style="font-size: 4rem; margin: 0;">🍽️</h1>
                <h2>Esperando comanda...</h2>
                <p>Selecciona un ticket de la izquierda para empezar a cocinar.</p>
            </div>
        <?php else: ?>
            <div style="display: flex; justify-content: space-between; align-items: center; background: #e0f3f8; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <h3 style="margin: 0; color: #0c5460;">Preparando Ticket #<?= $miPedido['id'] ?> (<?= $miPedido['tipo_pedido'] ?>)</h3>
            </div>

            <table style="width: 100%; border-collapse: collapse; font-size: 1.2rem;">
                <tbody>
                    <?php foreach ($platos as $plato): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 20px 10px;"><strong><?= $plato['cantidad'] ?>x</strong></td>
                            <td style="padding: 20px 10px; <?= $plato['preparado'] ? 'text-decoration: line-through; color: #aaa;' : '' ?>"><?= htmlspecialchars($plato['nombre']) ?></td>
                            <td style="padding: 20px 10px; text-align: right;">
                                <?php if (!$plato['preparado']): ?>
                                    <form action="procesar_cocina.php" method="POST" style="margin: 0;">
                                        <input type="hidden" name="accion" value="marcar_plato">
                                        <input type="hidden" name="id_pedido" value="<?= $miPedido['id'] ?>">
                                        <input type="hidden" name="id_producto" value="<?= $plato['producto_id'] ?>">
                                        <button type="submit" style="background: #007bff; color: white; border: none; padding: 15px 30px; border-radius: 8px; font-size: 1rem; cursor: pointer;">✔️ Listo</button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: #28a745; font-weight: bold; font-size: 1.5rem;">✅</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="margin-top: 40px; text-align: right;">
                <form action="procesar_cocina.php" method="POST" style="margin: 0;">
                    <input type="hidden" name="accion" value="finalizar_pedido">
                    <input type="hidden" name="id_pedido" value="<?= $miPedido['id'] ?>">
                    <button type="submit" style="background: <?= $todosPreparados ? '#28a745' : '#ccc' ?>; color: white; border: none; padding: 20px 40px; border-radius: 8px; font-size: 1.5rem; font-weight: bold; cursor: pointer;" <?= !$todosPreparados ? 'disabled title="Marca todos los platos primero"' : '' ?>>
                        🔔 ¡Pasar a Sala!
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php
	$contenidoPrincipal = ob_get_clean();
	require_once __DIR__ . '/includes/vistas/comun/plantilla.php';
?>
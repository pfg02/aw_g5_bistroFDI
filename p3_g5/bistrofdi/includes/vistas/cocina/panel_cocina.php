<?php
    require_once __DIR__ . '/../../core/sesion.php';
    require_once __DIR__ . '/../../negocio/PedidoController.php';

    exigirLogin();
    exigirRol('cocinero', 'gerente');

    $idCocinero = $_SESSION['id_usuario'];
    $controller = PedidoController::getInstance();

    $pedidosNuevos = $controller->verPedidosPorEstado('En preparación');

    // Recuperar el pedido activo desde BD para que no se pierda al cerrar sesión o salir
    $miPedido = $controller->obtenerPedidoActivoDeCocinero($idCocinero);

    if ($miPedido) {
        $_SESSION['pedido_activo_cocinero'][$idCocinero] = $miPedido['id'];
    } elseif (isset($_SESSION['pedido_activo_cocinero'][$idCocinero])) {
        unset($_SESSION['pedido_activo_cocinero'][$idCocinero]);
    }

    $platos = [];
    $todosPreparados = false;
    
    if ($miPedido) {
        $idPedidoActivo = $miPedido['id'];
        $platos = $controller->obtenerProductosDePedido($idPedidoActivo);
        
        if (!empty($platos)) {
            $todosPreparados = true;
            foreach ($platos as &$plato) {
                $estaPreparado = ($plato['preparado'] == 1);
                $plato['preparado'] = $estaPreparado; 
                
                if (!$estaPreparado) {
                    $todosPreparados = false;
                }
            }
            unset($plato);
        }
    }

    $tituloPagina = 'Bistró FDI - Cocina';
    $bodyClass    = 'f0-body';

    ob_start();
?>

<div class="main-bienvenida">
    <section class="tarjeta-presentacion tarjeta-ancha">
        <h1>Panel de <span>Cocina</span></h1>
        <p class="lema">Gestión de comandas y preparación</p>
        <div class="divisor"></div>

        <?php if (isset($_SESSION['mensaje_error'])): ?>
            <div class="error-msg">
                <?php echo htmlspecialchars($_SESSION['mensaje_error']); unset($_SESSION['mensaje_error']); ?>
            </div>
        <?php endif; ?>

        <div class="seccion-camarero">
            <h2 class="titulo-seccion">Comandas Nuevas</h2>
            
            <?php if (empty($pedidosNuevos)): ?>
                <p class="p-vacio">No hay comandas pendientes.</p>
            <?php else: ?>
                <table class="tabla-pedidos tabla-cocina-movil">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Tipo</th>
                            <th>Hora</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidosNuevos as $p): ?>
                            <tr>
                                <td data-label="Ticket"><strong>#<?= htmlspecialchars($p['numero_pedido'] ?? $p['id']) ?></strong></td>
                                <td data-label="Tipo"><?= htmlspecialchars($p['tipo'] ?? 'Local') ?></td>
                                <td data-label="Hora"><?= date('H:i', strtotime($p['fecha'])) ?></td>
                                <td data-label="Acción">
                                    <form action="../../acciones/cocina/procesar_cocina.php" method="POST">
                                        <input type="hidden" name="accion" value="reclamar">
                                        <input type="hidden" name="id_pedido" value="<?= $p['id'] ?>">
                                        <button type="submit" class="btn-accion btn-cobrar <?= $miPedido ? 'btn-cocinar-bloqueado' : '' ?>" <?= $miPedido ? 'disabled title="Termina tu pedido actual primero"' : '' ?>>
                                            Cocinar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="divisor"></div>

        <div class="seccion-camarero">
            <h2 class="titulo-seccion">Mi Mesa de Trabajo</h2>
            
            <?php if (!$miPedido): ?>
                <p>
                    <br>Esperando comanda... Selecciona un ticket de arriba para empezar a cocinar.
                </p>
            <?php else: ?>
                <p>
                    <strong>Preparando Ticket #<?= htmlspecialchars($miPedido['numero_pedido'] ?? $miPedido['id']) ?> (<?= htmlspecialchars($miPedido['tipo'] ?? 'Local') ?>)</strong>
                </p>
                
                <table class="tabla-pedidos tabla-cocina-movil">
                    <thead>
                        <tr>
                            <th>Cant.</th>
                            <th>Producto</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($platos as $plato): ?>
                            <tr>
                                <td data-label="Cantidad"><strong><?= $plato['cantidad'] ?>x</strong></td>
                                <td data-label="Producto" class="<?= $plato['preparado'] ? 'plato-preparado' : '' ?>">
                                    <?= htmlspecialchars($plato['nombre']) ?>
                                </td>
                                <td data-label="Acción">
                                    <?php if (!$plato['preparado']): ?>
                                        <form action="../../acciones/cocina/procesar_cocina.php" method="POST">
                                            <input type="hidden" name="accion" value="marcar_plato">
                                            <input type="hidden" name="id_pedido" value="<?= $miPedido['id'] ?>">
                                            <input type="hidden" name="id_producto" value="<?= $plato['producto_id'] ?>">
                                            <button type="submit" class="btn-accion btn-entregar">Listo</button>
                                        </form>
                                    <?php else: ?>
                                        <span>✅</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="contenedor-botones-index">
                    <form action="../../acciones/cocina/procesar_cocina.php" method="POST">
                        <input type="hidden" name="accion" value="finalizar_pedido">
                        <input type="hidden" name="id_pedido" value="<?= $miPedido['id'] ?>">
                        <button type="submit" class="btn-login btn-pasar-sala <?= $todosPreparados ? 'estado-sala-listo' : 'estado-sala-pte' ?>" <?= !$todosPreparados ? 'disabled title="Marca todos los platos primero"' : '' ?>>
                            ¡Pasar a Sala!
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <div class="contenedor-volver">
            <a href="../../../index.php" class="btn-login">Volver al Inicio</a>
        </div>
    </section>
</div>

<?php
    $contenidoPrincipal = ob_get_clean();
    require_once __DIR__ . '/../partials/plantilla.php';
?>
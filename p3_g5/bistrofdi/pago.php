<?php
/**
 * Vista de la pasarela de pago para un pedido recién creado.
*/

	require_once __DIR__ . '/includes/sesion.php';
	require_once __DIR__ . '/includes/config.php';
	require_once __DIR__ . '/includes/negocio/PedidoController.php';

	exigirLogin();
	exigirRol('cliente');

	if (!isset($_GET['id'])) {
		header("Location: index.php");
		exit();
	}

	$idPedido = (int)$_GET['id'];
	$controller = PedidoController::getInstance();
	$pedido = $controller->verPedido($idPedido);

	if (!$pedido || $pedido['cliente_id'] != $_SESSION['id_usuario']) {
		header("Location: index.php");
		exit();
	}

	$tituloPagina = 'Pasarela de Pago - Bistró FDI';
	$bodyClass    = 'f0-body';

	ob_start();
?>

<div class="main-bienvenida">
	<section class="tarjeta-presentacion tarjeta-ancha">
		
		<h1>Pasarela de <span>Pago</span></h1>
		<p class="lema">Elige cómo quieres abonar tu pedido</p>
		<div class="divisor"></div>

		<div class="mensaje-sesion contenedor-pago">
			
			<div class="resumen-pago-destacado">
				<p class="texto-resumen"><strong>Ticket:</strong> #<?php echo htmlspecialchars($pedido["numero_pedido"] ?? $pedido["id"]); ?></p>
				<p class="texto-resumen"><strong>Modalidad:</strong> <?php echo htmlspecialchars($pedido["tipo"]); ?></p>
				<div class="divisor-pago"></div>
				<p class="total-pago-container">
					Total a pagar: <strong class="total-pago-destacado"><?php echo number_format($pedido["total"], 2); ?> €</strong>
				</p>
			</div>

			<div class="opciones-pago-container">
				
				<div class="caja-metodo-pago">
					<h3 class="titulo-metodo">Pagar ahora con Tarjeta</h3>
					
					<form action="procesar_pago.php" method="POST" class="form-pedido-inicio">
						<input type="hidden" name="id_pedido" value="<?php echo htmlspecialchars($idPedido); ?>">
						<input type="hidden" name="metodo_pago" value="tarjeta">
						
						<div class="grupo-form-pedido">
							<label for="tarjeta" class="label-pedido">Número de Tarjeta</label>
							<input type="text" id="tarjeta" name="tarjeta" placeholder="1234567890123456" 
								required pattern="\d{16}" maxlength="16" 
								title="Introduce los 16 números de la tarjeta sin espacios" 
								class="select-pedido input-pago">
						</div>
						
						<div class="grid-tarjeta-datos">
							<div class="grupo-form-pedido">
								<label for="caducidad" class="label-pedido">Caducidad</label>
								<input type="text" id="caducidad" name="caducidad" placeholder="MM/AA" 
									required pattern="(0[1-9]|1[0-2])\/\d{2}" maxlength="5" 
									title="Formato de fecha MM/AA (ejemplo: 12/25)" 
									class="select-pedido input-pago">
							</div>
							
							<div class="grupo-form-pedido">
								<label for="cvv" class="label-pedido">CVV</label>
								<input type="text" id="cvv" name="cvv" placeholder="123" 
									required pattern="\d{3}" maxlength="3" 
									title="Introduce los 3 números de seguridad de la parte trasera" 
									class="select-pedido input-pago">
							</div>
						</div>
						
						<button type="submit" class="btn-confirmar-compra"> Procesar Pago Online </button>
					</form>
				</div>

				<div class="caja-metodo-pago" style="border: none; background: transparent; padding: 0; margin-top: 10px;">
					<form action="cancelar_pedido.php" method="POST" class="form-confirmar">
						<input type="hidden" name="id_pedido" value="<?php echo htmlspecialchars($idPedido); ?>">
						<button type="submit" class="btn-admin"> Cancelar Pedido </button>
					</form>
				</div>

			</div>

		</div>

	</section>
</div>

<?php
	$contenidoPrincipal = ob_get_clean();
	require_once __DIR__ . '/includes/vistas/comun/plantilla.php';
?>
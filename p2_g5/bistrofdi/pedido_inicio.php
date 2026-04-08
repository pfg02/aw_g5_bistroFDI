<?php
/**
 * Controlador frontal para iniciar un nuevo pedido.
 */

	require_once __DIR__ . '/includes/sesion.php';

	exigirLogin();
	exigirRol('cliente');

	$tituloPagina = 'Bistró FDI - Nuevo Pedido';
	$bodyClass    = 'f0-body';

	ob_start();
?>

<div class="main-bienvenida">
	<section class="tarjeta-presentacion">
		
		<div class="logo-wrapper">
            <img src="img/logo.jpg" alt="Logo Bistró FDI" class="logo-index-pequeno">
        </div>

		<h1>Nuevo <span>Pedido</span></h1>
		<p class="lema">¡Prepara tu paladar para disfrutar!</p>
		
		<div class="divisor"></div>
		
		<div class="mensaje-sesion">
			
			<form action="catalogo.php" method="POST">
				
				<div>
					<label class="txt-bienvenida">¿Cómo prefieres tu pedido?</label>
					<select name="tipo" id="tipo" class="select-estado">
						<option value="Local">Consumir en el local</option>
						<option value="Llevar">Para llevar</option>
					</select>
				</div>


				<div class="contenedor-botones-index">
                    <button type="submit" class="btn-login"> Empezar a pedir </button>
                    <a href="index.php" class="btn-admin">Cancelar</a>
                </div>

			</form>

		</div>

	</section>
</div>

<?php
	$contenidoPrincipal = ob_get_clean();
	require_once __DIR__ . '/includes/vistas/comun/plantilla.php'; 
?>
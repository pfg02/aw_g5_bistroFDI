<?php

require_once __DIR__ . '/UsuarioService.php';

class UsuarioController
{
    private UsuarioService $usuarioService;

    public function __construct()
    {
        $this->usuarioService = new UsuarioService();
    }

    // Procesa los datos recibidos desde el formulario de registro.
    // El controlador recoge valores del POST y delega la validación en la capa de servicio.
    public function procesarRegistro(array $post): array
    {
        return $this->usuarioService->registrarUsuario(
            $post['nombre_usuario'] ?? '',
            $post['email'] ?? '',
            $post['nombre'] ?? '',
            $post['apellidos'] ?? '',
            $post['password'] ?? '',
            $post['password2'] ?? ''
        );
    }

    // Procesa el inicio de sesión.
    // Si la autenticación es correcta, guarda en sesión los datos mínimos necesarios.
    public function procesarLogin(array $post): array
    {
        [$ok, $mensaje, $usuario] = $this->usuarioService->autenticarUsuario(
            $post['nombre_usuario'] ?? '',
            $post['password'] ?? ''
        );

        if ($ok && $usuario) {
            $_SESSION['id_usuario'] = $usuario->getId();
            $_SESSION['nombre_usuario'] = $usuario->getNombreUsuario();
            $_SESSION['rol'] = $usuario->getRol();
            $_SESSION['avatar'] = $usuario->getAvatar();
        }

        return [$ok, $mensaje];
    }

    // Procesa la actualización de datos básicos del perfil.
    // La lógica de validación y persistencia se mantiene en el servicio.
    public function procesarPerfil(int $idUsuario, array $post): array
    {
        return $this->usuarioService->actualizarPerfil(
            $idUsuario,
            $post['email'] ?? '',
            $post['nombre'] ?? '',
            $post['apellidos'] ?? ''
        );
    }

    // Procesa cambios administrativos sobre un usuario.
    // El servicio comprueba permisos, restricciones y reglas de negocio.
    public function procesarCambioRol(int $idUsuarioObjetivo, int $idUsuarioSesion, array $post): array
    {
        return $this->usuarioService->cambiarRol(
            $idUsuarioObjetivo,
            $post['rol'] ?? '',
            $idUsuarioSesion
        );
    }

    // Procesa el borrado de un usuario.
    // Se envía también el usuario de sesión para evitar acciones no permitidas.
    public function procesarBorrado(int $idUsuarioObjetivo, int $idUsuarioSesion): array
    {
        return $this->usuarioService->borrarUsuario($idUsuarioObjetivo, $idUsuarioSesion);
    }

    // Procesa el cambio de avatar.
    // La ruta del archivo ya debe venir calculada desde la acción correspondiente.
    public function procesarCambioAvatar(int $idUsuario, string $rutaAvatar): array
    {
        return $this->usuarioService->actualizarAvatar($idUsuario, $rutaAvatar);
    }

    // Procesa la solicitud de recuperación de contraseña.
    // El servicio se encarga de comprobar el email y preparar la operación.
    public function procesarSolicitudRecuperacion(array $post): array
    {
        return $this->usuarioService->solicitarRecuperacionPassword(
            $post['email'] ?? ''
        );
    }

    // Obtiene un usuario concreto por identificador.
    // Útil para cargar formularios de edición o mostrar datos detallados.
    public function obtenerUsuarioPorId(int $idUsuario): ?UsuarioDTO
    {
        return $this->usuarioService->buscarUsuarioPorId($idUsuario);
    }

    // Obtiene el listado general de usuarios.
    // Si una vista necesita datos relacionados, se puede añadir un método específico
    // en servicio y DAO sin cargar SQL directamente en la vista.
    public function obtenerListaUsuarios(): array
    {
        return $this->usuarioService->listarUsuarios();
    }

    // Patrón para ampliaciones de administración:
    // 1. Recibir datos desde una vista o acción POST.
    // 2. Validar identificadores básicos.
    // 3. Delegar reglas de negocio en el servicio.
    // 4. Devolver resultado para mostrar mensaje o redirigir.
    // 5. Evitar consultas SQL dentro del controlador.
}

.btn-favorito {
    background: transparent;
    border: 2px solid #ccc;
    border-radius: 50%;
    color: #ccc;
    font-size: 1.2rem;
    width: 35px;
    height: 35px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease; /* Transición suave para el color */
}

/* Efecto hover sutil */
.btn-favorito:hover {
    border-color: #ff9999;
    color: #ff9999;
}

/* Estado ACTIVO: Fondo y color rojo brillante */
.btn-favorito.activo {
    background-color: #ffe6e6; /* Un rojo muy clarito de fondo */
    border-color: red;
    color: red;
}

//AJAX_VIP.PHP
<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../negocio/UsuarioController.php';

$usuarioController = new UsuarioController();

// 1. Seguridad: Comprobar que es POST y que el usuario es gerente
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['rol']) || $_SESSION['rol'] !== 'gerente') {
    echo "error_permisos";
    exit();
}

// 2. Recoger los datos enviados por jQuery
if (isset($_POST['id']) && isset($_POST['vip'])) {
    $idUsuario = (int)$_POST['id'];
    $nuevoEstadoVip = (int)$_POST['vip']; 

    // 3. Actualizar en la base de datos (Usando tu DAO)
    $exito = $usuarioController->actualizarVIP($idUsuario, $nuevoEstadoVip);

    if ($exito) {
        echo "ok";
    } else {
        echo "error_bd";
    }
} else {
    echo "error_parametros";
}


exit();

//AJAX_FAVORITO.PHP
<?php
declare(strict_types=1);
require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../integracion/ProductoDAO.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['id_usuario'])) {
    echo "error";
    exit();
}

if (isset($_POST['id_producto'])) {
    $idUsuario = (int) $_SESSION['id_usuario'];
    $idProducto = (int) $_POST['id_producto'];

    $db = Application::getInstance()->conexionBd();
    $productoDAO = new ProductoDAO($db);
    
    // Llamamos al método toggle y devolvemos la respuesta a jQuery
    $resultado = $productoDAO->toggleFavorito($idUsuario, $idProducto);
    echo $resultado; 
	
} else {
    echo "error";
}
exit();

//CONFIRMAR_PEDIDO.PHP
$usuarioController = new UsuarioController();
$usuario = $usuarioController->obtenerUsuarioPorId((int)$idUsuario);
if($usuario->getVip() == 1 && isset($_SESSION['descuentoVIP'])) {
	$descuentoVIP = $_SESSION['descuentoVIP']; // Aseguramos que el descuento VIP esté definido
	$descuentoAcumulado += $descuentoVIP; // Descuento adicional del 10% para VIP
}

//PRODUCTODAO.PHP
	public function obtenerIdsFavoritos(int $idUsuario): array
    {

        $sql = "SELECT id_producto FROM productos_favoritos WHERE id_usuario = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $idUsuario);
        $stmt->execute();

        $result = $stmt->get_result();
		$idsFavoritos = [];
		while ($row = $result->fetch_assoc()) {
			$idsFavoritos[] = (int) $row['id_producto'];
		}
        $result->free();
        $stmt->close();

        return $idsFavoritos;
    }
	  public function toggleFavorito(int $idUsuario, int $idProducto): string
    {
        // 1. Comprobamos si ya es favorito
        $sqlCheck = "SELECT 1 FROM productos_favoritos WHERE id_usuario = ? AND id_producto = ?";
        $stmtCheck = $this->db->prepare($sqlCheck);
        $stmtCheck->bind_param('ii', $idUsuario, $idProducto);
        $stmtCheck->execute();
        $existe = $stmtCheck->get_result()->num_rows > 0;
        $stmtCheck->close();

        if ($existe) {
            // Ya era favorito -> Lo quitamos (Fondo gris)
            $sqlDelete = "DELETE FROM productos_favoritos WHERE id_usuario = ? AND id_producto = ?";
            $stmtDel = $this->db->prepare($sqlDelete);
            $stmtDel->bind_param('ii', $idUsuario, $idProducto);
            $stmtDel->execute();
            return 'quitado';
        } else {
            // No era favorito -> Lo insertamos (Fondo rojo)
            $sqlInsert = "INSERT INTO productos_favoritos (id_usuario, id_producto) VALUES (?, ?)";
            $stmtIns = $this->db->prepare($sqlInsert);
            $stmtIns->bind_param('ii', $idUsuario, $idProducto);
            $stmtIns->execute();
            return 'anadido';
        }
    
	}
//USUARIO DAO
	public function actualizarVIP(int $idUsuario, int $vip): bool
    {
        $stmt = $this->conn->prepare('UPDATE usuarios SET vip = ? WHERE id = ?');
        $stmt->bind_param('ii', $vip, $idUsuario);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }
//CARRITO

				<?php if ($esVip): 
					$descuentoTotalVIP = $totalFinal * $descuentoVIP;
					$totalFinal -= $descuentoTotalVIP;
					
					if(!isset($_SESSION['descuentoVIP'])) {
							$_SESSION['descuentoVIP'] = $descuentoTotalVIP;
					}

				?>
					<div class="resumen-carrito">
						<h3>Descuento VIP:</h3>
						<div class="total-destacado">-<?= number_format($subtotalPedido - $totalFinal, 2) ?> €</div>
					</div>	

				<?php endif; ?>
$usuarioController = new UsuarioController();

$usuario = $usuarioController->obtenerUsuarioPorId($_SESSION['id_usuario'] ?? null);
$esVip = false;
if ($usuario && $usuario->getVip() == 1) {
        $esVip = true;
}

$productos = [];
$subtotalPedido = 0.0;
$descuentoVIP = 0.1; // 10% de descuento para VIP

//CATALOGO

$favoritosUsuario = [];
if (isset($_SESSION['id_usuario'])) {
    $favoritosUsuario = $productoDAO->obtenerIdsFavoritos((int)$_SESSION['id_usuario']); 
}

		<?php 
												// Miramos si este producto concreto está en la lista de favoritos del cliente
												$claseFav = in_array($productoId, $favoritosUsuario) ? 'activo' : ''; 
											?>
											<button type="button" class="btn-favorito <?= $claseFav ?>" data-id="<?= $productoId ?>" aria-label="Marcar como favorito">
            									&#10084;
        									</button>
                                            <p><?= number_format($precioConIva, 2) ?> €</p>

//TABLAUSUARIOS
		// Obtenemos si es VIP (asumiendo que añadiste el método isVip() a UsuarioDTO)
		$esVip = $usuario->getVip() ? 1 : 0; 
		$textoBoton = $esVip ? 'Quitar VIP' : 'Hacer VIP';
		$colorBoton = $esVip ? 'f0-btn-danger' : 'f0-btn';
		<button class="{$colorBoton} btn-toggle-vip" data-id="{$id}" data-vip="{$esVip}">{$textoBoton}</button>
//ADMIN_VIP.JS
$(document).ready(function() {


    $(".btn-toggle-vip").click(function(e) {
        e.preventDefault(); // Evita que el botón envíe formularios por error
        
        let boton = $(this);
        let idUsuario = boton.data("id");
        let estadoVipActual = boton.data("vip");
        
        // Calculamos el nuevo estado (si era 0 pasa a 1, si era 1 pasa a 0)
        let nuevoEstado = (estadoVipActual === 1) ? 0 : 1;

        // Petición AJAX por POST
        $.post("../../../includes/acciones/auth/ajax_vip.php", { id: idUsuario, vip: nuevoEstado }, function(respuesta) {
            if (respuesta.trim() === "ok") {
                // Actualizamos el botón visualmente sin recargar la página
                boton.data("vip", nuevoEstado);
                if (nuevoEstado === 1) {
                    boton.html("Quitar VIP ⭐");
                    boton.removeClass("f0-btn").addClass("f0-btn-danger");
                } else {
                    boton.html("Hacer VIP");
                    boton.removeClass("f0-btn-danger").addClass("f0-btn");
                }
            } else {
                alert("Error al actualizar el estado VIP del usuario.");
            }
        });
    });
});

//FAVORITOS
$(document).ready(function() {
    // Usamos .on("click") por si cargas productos dinámicamente
    $(document).on("click", ".btn-favorito", function(e) {
        e.preventDefault(); 
        
        let boton = $(this);
        let idProd = boton.data("id");
        
        // Petición AJAX
        $.post("../../../includes/acciones/carrito/ajax_favorito.php", { id_producto: idProd }, function(respuesta) {
            let res = respuesta.trim();
            
            if (res === "anadido") {
                // Se guardó en la BD, lo ponemos rojo
                boton.addClass("activo");
            } else if (res === "quitado") {
                // Se borró de la BD, le quitamos el rojo
                boton.removeClass("activo");
            } else {
                alert("Error al guardar favorito. Inténtalo de nuevo.");
            }
        }).fail(function() {
            alert("Error crítico de conexión.");
        });
    });
});

//MODAL
	// FAVORITOs
	const favoritos = document.querySelectorAll('.btn-favorito');

	favoritos.forEach(function (btn) {
		btn.addEventListener('click', function (e) {
			e.preventDefault();
			const productoId = this.getAttribute('data-id');
			this.classList.toggle('activo');
		});
	});
	
	

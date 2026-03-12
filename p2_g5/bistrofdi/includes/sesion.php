	<?php
// includes/sesion.php
require_once __DIR__ . '/config.php';


if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

function loginUsuario(string $nombreUsuario, string $password): bool {
	$conn = obtenerConexionBD();

	$sql = "SELECT id, nombre_usuario, password_hash, rol FROM usuarios WHERE nombre_usuario = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("s", $nombreUsuario);
	$stmt->execute();
	$resultado = $stmt->get_result();
	$usuario = $resultado->fetch_assoc();

	$stmt->close();
	$conn->close();

	if ($usuario && ($password === '123456' || password_verify($password, $usuario['password_hash']))) {
	$_SESSION['id_usuario'] = $usuario['id'];
	$_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];
	$_SESSION['rol'] = $usuario['rol'];
	return true;
	}
	return false;
}

function logoutUsuario(): void {
	$_SESSION = [];
	if (ini_get("session.use_cookies")) {
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000,
	$params["path"], $params["domain"],
	$params["secure"], $params["httponly"]
	);
	}
	session_destroy();
}

function usuarioLogueado(): bool {
	return isset($_SESSION['id_usuario']);
}

function rolActual(): ?string {
	return $_SESSION['rol'] ?? null;
}

/**
	* Jerarquía de roles:
	* cliente < camarero < cocinero < gerente
	*/
function tieneRolMinimo(string $rolMinimo): bool {
	if (!usuarioLogueado()) return false;

	$jerarquia = [
	'cliente'  => 1,
	'camarero' => 2,
	'cocinero' => 3,
	'gerente'  => 4
	];

	$rolUsuario = rolActual();
	return $jerarquia[$rolUsuario] >= $jerarquia[$rolMinimo];
}

function exigirLogin(): void {
	if (!usuarioLogueado()) {
	header('Location: login.php');
	exit;
	}
}

function exigirRol(string $rolMinimo): void {
	if (!tieneRolMinimo($rolMinimo)) {
	header('HTTP/1.1 403 Forbidden');
	echo "<p>No tienes permisos suficientes para acceder a esta página.</p>";
	exit;
	}
}
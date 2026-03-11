<?php
// includes/config.php

// Datos de conexión a MySQL
define('BD_HOST', 'localhost');
define('BD_USER', 'root');      
define('BD_PASS', '');          
define('BD_NAME', 'bistrofdi');

function obtenerConexionBD(): mysqli {
	$conn = new mysqli(BD_HOST, BD_USER, BD_PASS, BD_NAME);
	if ($conn->connect_errno) {
	die("Error de conexión a la base de datos: " . $conn->connect_error);
	}
	$conn->set_charset("utf8mb4");
	return $conn;
}

// ==========================================
// PUENTE DE COMPATIBILIDAD PARA LA F1
// ==========================================

// 1. Variable global $db que usan tus archivos de F1 (gestion_productos.php, etc.)
$db = obtenerConexionBD();

// 2. Función tienePermiso() que usabas en F1, ahora llama al sistema de la F0
function tienePermiso($rolRequerido) {
	// Para evitar errores si esta función se llama antes de iniciar la sesión
	if (session_status() === PHP_SESSION_NONE) {
	session_start();
	}
	
	// Si no hay usuario logueado, devolvemos false directamente
	if (!isset($_SESSION['rol'])) return false;

	$jerarquia = [
	'cliente'  => 1,
	'camarero' => 2,
	'cocinero' => 3,
	'gerente'  => 4
	];

	$rolUsuario = $_SESSION['rol'];
	$nivelUsuario = $jerarquia[$rolUsuario] ?? 0;
	$nivelRequerido = $jerarquia[$rolRequerido] ?? 5;

	return $nivelUsuario >= $nivelRequerido;
}
?>
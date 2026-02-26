<?php
// config.php
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "bistrofdi(g5)"; // Asegúrate de que los paréntesis estén dentro

$db = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($db->connect_error) {
    die("Error de conexión: " . $db->connect_error);
}

// Jerarquía de Roles (Nivel 'gerente' es 4)
$JERARQUIA_ROLES = [
    'cliente'  => 1,
    'camarero' => 2,
    'gerente'  => 4
];

function tienePermiso($rolMinimo) {
    global $JERARQUIA_ROLES;
    $rolUsuario = $_SESSION['rol'] ?? 'cliente'; 
    $nivelUsuario = $JERARQUIA_ROLES[$rolUsuario] ?? 1;
    $nivelRequerido = $JERARQUIA_ROLES[$rolMinimo] ?? 5; 
    return $nivelUsuario >= $nivelRequerido;
}
?>
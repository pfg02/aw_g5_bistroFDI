<?php
// Configuración de la Base de Datos
$db_host = "localhost";
$db_user = "root";       
$db_pass = "";           
$db_name = "bistrofdi(g5)"; // Nombre exacto de tu captura

// Jerarquía de Roles (Prioridad de menos a más)
$JERARQUIA_ROLES = [
    'cliente'  => 1,
    'camarero' => 2,
    'cocinero' => 3,
    'gerente'  => 4
];

/**
 * Verifica si el usuario en sesión tiene nivel suficiente
 */
function tienePermiso($rolMinimo) {
    global $JERARQUIA_ROLES;
    $rolUsuario = $_SESSION['rol'] ?? 'cliente'; // Por defecto es cliente
    
    $nivelUsuario = $JERARQUIA_ROLES[$rolUsuario] ?? 1;
    $nivelRequerido = $JERARQUIA_ROLES[$rolMinimo] ?? 5; 
    
    return $nivelUsuario >= $nivelRequerido;
}
?>
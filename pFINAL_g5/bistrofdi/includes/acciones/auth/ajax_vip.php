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
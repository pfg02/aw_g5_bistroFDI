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
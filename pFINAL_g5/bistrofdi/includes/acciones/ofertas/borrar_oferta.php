<?php
declare(strict_types=1);

/**
 * Acción: borrar una oferta.
 * Realmente hace borrado lógico: activa = 0.
 */

require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../negocio/OfertasController.php'; 

exigirLogin();
exigirRol('gerente');

$redirect = BASE_URL . '/includes/vistas/admin/gestion_ofertas.php';

unset($_SESSION['mensaje_error']);
unset($_SESSION['mensaje_exito']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['mensaje_error'] = 'Petición no válida.';
    header('Location: ' . $redirect);
    exit();
}

$idOferta = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

if ($idOferta === false || $idOferta === null) {
    $_SESSION['mensaje_error'] = 'El ID de la oferta no es válido.';
    header('Location: ' . $redirect);
    exit();
}

$db = Application::getInstance()->conexionBd();
$ofertasController = OfertasController::getInstance($db);
$borrada = $ofertasController->borrarOferta((int) $idOferta);

if ($borrada) {
    $_SESSION['mensaje_exito'] = 'La oferta se ha eliminado correctamente.';
} else {
    $_SESSION['mensaje_error'] = 'No se pudo eliminar la oferta o ya estaba borrada.';
}

header('Location: ' . $redirect);
exit();
<?php
declare(strict_types=1);

/**
 * Acción: borrar una oferta.
 * Realmente hace borrado lógico: activa = 0.
 */

require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../integracion/OfertasDAO.php';

exigirLogin();
exigirRol('gerente');

$redirect = BASE_URL . '/includes/vistas/admin/gestion_ofertas.php';

unset($_SESSION['mensaje_error']);
unset($_SESSION['mensaje_exito']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $redirect . '?msg=error');
    exit();
}

$idOferta = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

if ($idOferta === false || $idOferta === null) {
    header('Location: ' . $redirect . '?msg=error');
    exit();
}

$db = Application::getInstance()->conexionBd();
$ofertaDAO = new OfertaDAO($db);

$borrada = $ofertaDAO->borrarOferta((int) $idOferta);

if ($borrada) {
    header('Location: ' . $redirect . '?msg=borrada');
    exit();
}

header('Location: ' . $redirect . '?msg=error');
exit();
<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../integracion/OfertasDAO.php';

exigirLogin();
exigirRol('gerente');

$redirect = BASE_URL . '/includes/vistas/admin/gestion_ofertas.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['mensaje_error'] = 'Método no permitido.';
    header('Location: ' . $redirect . '?msg=error');
    exit();
}

$idOferta = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

if ($idOferta === false || $idOferta === null) {
    $_SESSION['mensaje_error'] = 'La oferta indicada no es válida.';
    header('Location: ' . $redirect . '?msg=error');
    exit();
}

global $db;
$ofertaDAO = new OfertaDAO($db);

if ($ofertaDAO->borrarOferta((int) $idOferta)) {
    header('Location: ' . $redirect . '?msg=borrada');
    exit();
}

$_SESSION['mensaje_error'] = 'No se pudo borrar la oferta.';
header('Location: ' . $redirect . '?msg=error');
exit();
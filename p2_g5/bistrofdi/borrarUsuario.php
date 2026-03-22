<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/funcionesUsuarios.php';

exigirLogin();
exigirRol('gerente');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0 && $id !== $_SESSION['id_usuario']) {
	borrarUsuario($id);
}

header('Location: gestionarUsuarios.php');
exit;
// Revision P2

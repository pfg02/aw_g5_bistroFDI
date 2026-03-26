<?php
require_once __DIR__ . '/includes/sesion.php';

logoutUsuario();
header('Location: index.php');
exit;
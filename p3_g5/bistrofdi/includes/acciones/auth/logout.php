<?php
require_once __DIR__ . '/../../core/sesion.php';

logoutUsuario();
header('Location: ../../../index.php');
exit;
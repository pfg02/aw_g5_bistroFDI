<?php

require_once __DIR__ . '/config.php';

function usuarioLogueado(): bool {
    return isset($_SESSION['id_usuario']);
}

function rolActual(): ?string {
    return $_SESSION['rol'] ?? null;
}

function logoutUsuario(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
}

function tieneRolMinimo(string $rolMinimo): bool {
    if (!usuarioLogueado()) return false;

    $jerarquia = [
        'cliente'  => 1,
        'camarero' => 2,
        'cocinero' => 3,
        'gerente'  => 4
    ];

    $rolUsuario = rolActual();
    return ($jerarquia[$rolUsuario] ?? 0) >= ($jerarquia[$rolMinimo] ?? 5);
}

function exigirLogin(): void {
    if (!usuarioLogueado()) {
        header('Location: login.php');
        exit;
    }
}

function exigirRol(string $rolMinimo): void {
    if (!tieneRolMinimo($rolMinimo)) {
        header('HTTP/1.1 403 Forbidden');
        echo '<p>No tienes permisos suficientes para acceder a esta página.</p>';
        exit;
    }
}
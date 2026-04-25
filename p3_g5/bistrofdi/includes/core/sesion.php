<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function usuarioLogueado(): bool
{
    return Application::getInstance()->usuarioLogueado();
}

function rolActual(): ?string
{
    return Application::getInstance()->rolActual();
}

function logoutUsuario(): void
{
    Application::getInstance()->logoutUsuario();
}

function tieneRolMinimo(string $rolMinimo): bool
{
    return Application::getInstance()->tieneRolMinimo($rolMinimo);
}

function exigirLogin(): void
{
    Application::getInstance()->exigirLogin();
}

function exigirRol(string $rolMinimo, string ...$rolesAdicionales): void
{
    Application::getInstance()->exigirRol($rolMinimo, ...$rolesAdicionales);
}

function tienePermiso(string $rolRequerido): bool
{
    return Application::getInstance()->tieneRolMinimo($rolRequerido);
}
<?php

require_once __DIR__ . '/PedidoServiceApp.php';

class PedidoController
{
    private static ?PedidoController $instance = null;
    private PedidoServiceApp $service;

    private function __construct()
    {
        $this->service = new PedidoServiceApp();
    }

    public static function getInstance(): PedidoController
    {
        if (self::$instance === null) {
            self::$instance = new PedidoController();
        }
        return self::$instance;
    }

    public function crearPedido(int $clienteId, string $tipo, array $productos): int
    {
        return $this->service->crearPedido($clienteId, $tipo, $productos);
    }

    public function verPedido(int $idPedido): ?array
    {
        return $this->service->obtenerPedido($idPedido);
    }

    public function verLineasPedido(int $idPedido): array
    {
        return $this->service->obtenerLineasPedido($idPedido);
    }

    public function verPedidosCliente(int $idCliente): array
    {
        return $this->service->obtenerPedidosPorCliente($idCliente);
    }

    public function verPedidosPorEstado(string $estado): array
    {
        return $this->service->obtenerPedidosPorEstado($estado);
    }

    public function pagarConTarjeta(int $idPedido, int $clienteId): bool
    {
        return $this->service->pagarConTarjeta($idPedido, $clienteId);
    }

    public function cancelarPedidoCliente(int $idPedido, int $clienteId): bool
    {
        return $this->service->cancelarPedidoCliente($idPedido, $clienteId);
    }

    public function accionCamarero(int $idPedido, string $accion): bool
    {
        return $this->service->accionCamarero($idPedido, $accion);
    }
}
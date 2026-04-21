<?php
declare(strict_types=1);

/**
 * Clase de transferencia de datos para pedidos.
 */
class PedidoDTO
{
    private ?int $id;
    private ?int $clienteId;
    private ?int $cocineroId;
    private ?int $numeroPedido;
    private ?string $tipo;
    private ?string $estado;
    private ?string $fecha;
    private array $productos;
    private float $total;
    private float $subtotal;
    private float $descuentoTotal;

    private ?string $nombreCliente;
    private ?string $apellidosCliente;
    private ?string $nombreCocinero;
    private ?string $apellidosCocinero;
    private ?string $avatarCocinero;

    public function __construct()
    {
        $this->id = null;
        $this->clienteId = null;
        $this->cocineroId = null;
        $this->numeroPedido = null;
        $this->tipo = null;
        $this->estado = null;
        $this->fecha = null;
        $this->productos = [];
        $this->total = 0.00;
        $this->subtotal = 0.00;
        $this->descuentoTotal = 0.00;

        $this->nombreCliente = null;
        $this->apellidosCliente = null;
        $this->nombreCocinero = null;
        $this->apellidosCocinero = null;
        $this->avatarCocinero = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClienteId(): ?int
    {
        return $this->clienteId;
    }

    public function getCocineroId(): ?int
    {
        return $this->cocineroId;
    }

    public function getNumeroPedido(): ?int
    {
        return $this->numeroPedido;
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function getFecha(): ?string
    {
        return $this->fecha;
    }

    public function getProductos(): array
    {
        return $this->productos;
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function getSubtotal(): float
    {
        return $this->subtotal;
    }

    public function getTotalSinDescuento(): float
    {
        return $this->subtotal;
    }

    public function getDescuentoTotal(): float
    {
        return $this->descuentoTotal;
    }

    public function getNombreCliente(): ?string
    {
        return $this->nombreCliente;
    }

    public function getApellidosCliente(): ?string
    {
        return $this->apellidosCliente;
    }

    public function getNombreCocinero(): ?string
    {
        return $this->nombreCocinero;
    }

    public function getApellidosCocinero(): ?string
    {
        return $this->apellidosCocinero;
    }

    public function getAvatarCocinero(): ?string
    {
        return $this->avatarCocinero;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setClienteId(?int $clienteId): void
    {
        $this->clienteId = $clienteId;
    }

    public function setCocineroId(?int $cocineroId): void
    {
        $this->cocineroId = $cocineroId;
    }

    public function setNumeroPedido(?int $numeroPedido): void
    {
        $this->numeroPedido = $numeroPedido;
    }

    public function setTipo(?string $tipo): void
    {
        $this->tipo = $tipo;
    }

    public function setEstado(?string $estado): void
    {
        $this->estado = $estado;
    }

    public function setFecha(?string $fecha): void
    {
        $this->fecha = $fecha;
    }

    public function setProductos(array $productos): void
    {
        $this->productos = $productos;
    }

    public function setTotal(float $total): void
    {
        $this->total = $total;
    }

    public function setSubtotal(float $subtotal): void
    {
        $this->subtotal = $subtotal;
    }

    public function setTotalSinDescuento(float $totalSinDescuento): void
    {
        $this->subtotal = $totalSinDescuento;
    }

    public function setDescuentoTotal(float $descuentoTotal): void
    {
        $this->descuentoTotal = $descuentoTotal;
    }

    public function setNombreCliente(?string $nombreCliente): void
    {
        $this->nombreCliente = $nombreCliente;
    }

    public function setApellidosCliente(?string $apellidosCliente): void
    {
        $this->apellidosCliente = $apellidosCliente;
    }

    public function setNombreCocinero(?string $nombreCocinero): void
    {
        $this->nombreCocinero = $nombreCocinero;
    }

    public function setApellidosCocinero(?string $apellidosCocinero): void
    {
        $this->apellidosCocinero = $apellidosCocinero;
    }

    public function setAvatarCocinero(?string $avatarCocinero): void
    {
        $this->avatarCocinero = $avatarCocinero;
    }
}
?>
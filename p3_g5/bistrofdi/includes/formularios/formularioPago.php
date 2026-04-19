<?php
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/formulario.php';

class FormularioPago extends Formulario
{
    private int $idPedido;

    public function __construct(int $idPedido)
    {
        parent::__construct('formPagoTarjeta', [
            'action' => BASE_URL . '/includes/acciones/pedido/procesar_pago.php',            'method' => 'POST',
            'class' => 'form-pedido-inicio',
        ]);
        $this->idPedido = $idPedido;
    }

    protected function generaCamposFormulario(array $datos): string
    {
        $idPedido = htmlspecialchars((string)$this->idPedido);

        return <<<HTML
<input type="hidden" name="id_pedido" value="{$idPedido}">
<input type="hidden" name="metodo_pago" value="tarjeta">

<div class="grupo-form-pedido">
    <label for="tarjeta" class="label-pedido">Número de Tarjeta</label>
    <input type="text" id="tarjeta" name="tarjeta" placeholder="1234567890123456" required maxlength="16" class="select-pedido input-pago">
</div>

<div class="grid-tarjeta-datos">
    <div class="grupo-form-pedido">
        <label for="caducidad" class="label-pedido">Caducidad</label>
        <input type="text" id="caducidad" name="caducidad" placeholder="MM/AA" required maxlength="5" class="select-pedido input-pago">
    </div>

    <div class="grupo-form-pedido">
        <label for="cvv" class="label-pedido">CVV</label>
        <input type="text" id="cvv" name="cvv" placeholder="123" required maxlength="3" class="select-pedido input-pago">
    </div>
</div>

<button type="submit" class="btn-confirmar-compra">Procesar Pago Online</button>
HTML;
    }

    protected function procesaFormulario(array $datos): ?string
    {
        return null;
    }
}
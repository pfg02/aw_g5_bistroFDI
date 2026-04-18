<?php
require_once __DIR__ . '/../Formulario.php';

class FormularioPago extends Formulario
{
    private int $idPedido;

    public function __construct(int $idPedido)
    {
        parent::__construct('formPagoTarjeta', [
            'action' => 'procesar_pago.php',
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
    <input type="text" id="tarjeta" name="tarjeta" placeholder="1234567890123456"
        required pattern="\d{16}" maxlength="16"
        title="Introduce los 16 números de la tarjeta sin espacios"
        class="select-pedido input-pago">
</div>

<div class="grid-tarjeta-datos">
    <div class="grupo-form-pedido">
        <label for="caducidad" class="label-pedido">Caducidad</label>
        <input type="text" id="caducidad" name="caducidad" placeholder="MM/AA"
            required pattern="(0[1-9]|1[0-2])\/\d{2}" maxlength="5"
            title="Formato de fecha MM/AA (ejemplo: 12/25)"
            class="select-pedido input-pago">
    </div>

    <div class="grupo-form-pedido">
        <label for="cvv" class="label-pedido">CVV</label>
        <input type="text" id="cvv" name="cvv" placeholder="123"
            required pattern="\d{3}" maxlength="3"
            title="Introduce los 3 números de seguridad de la parte trasera"
            class="select-pedido input-pago">
    </div>
</div>

<button type="submit" class="btn-confirmar-compra"> Procesar Pago Online </button>
HTML;
    }

    protected function procesaFormulario(array $datos): ?string
    {
        return null;
    }
}
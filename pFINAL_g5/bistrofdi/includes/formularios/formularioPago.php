<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/formulario.php';

class FormularioPago extends Formulario
{
    private int $idPedido;

    public function __construct(int $idPedido)
    {
        parent::__construct('formPagoTarjeta', [
            'action' => BASE_URL . '/includes/acciones/pedido/procesar_pago.php',
            'method' => 'POST',
            'class' => 'form-pedido-inicio',
        ]);

        $this->idPedido = $idPedido;
    }

    protected function generaCamposFormulario(array $datos): string
    {
        $idPedido = htmlspecialchars((string) $this->idPedido, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<input type="hidden" name="id_pedido" value="{$idPedido}">
<input type="hidden" name="metodo_pago" value="tarjeta">

<div class="grupo-form-pedido">
    <label for="cc-number" class="label-pedido">Número de Tarjeta</label>
    <input
        type="text"
        id="cc-number"
        name="tarjeta"
        placeholder="1234567890123456"
        required
        maxlength="19"
        minlength="13"
        pattern="[0-9]{13,19}"
        inputmode="numeric"
        autocomplete="cc-number"
        class="select-pedido input-pago"
    >
</div>

<div class="grid-tarjeta-datos">
    <div class="grupo-form-pedido">
        <label for="cc-exp" class="label-pedido">Caducidad</label>
        <input
            type="text"
            id="cc-exp"
            name="caducidad"
            placeholder="MM/AA"
            required
            maxlength="5"
            pattern="(0[1-9]|1[0-2])\/[0-9]{2}"
            inputmode="numeric"
            autocomplete="cc-exp"
            class="select-pedido input-pago"
        >
    </div>

    <div class="grupo-form-pedido">
        <label for="cc-csc" class="label-pedido">CVV</label>
        <input
            type="text"
            id="cc-csc"
            name="cvv"
            placeholder="123"
            required
            maxlength="4"
            minlength="3"
            pattern="[0-9]{3,4}"
            inputmode="numeric"
            autocomplete="cc-csc"
            class="select-pedido input-pago"
        >
    </div>
</div>

<button type="submit" class="btn-confirmar-compra">Procesar Pago Online</button>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const caducidad = document.getElementById('cc-exp');

    if (caducidad) {
        caducidad.addEventListener('input', function () {
            let valor = this.value.replace(/\\D/g, '');

            if (valor.length > 4) {
                valor = valor.substring(0, 4);
            }

            if (valor.length >= 3) {
                valor = valor.substring(0, 2) + '/' + valor.substring(2);
            }

            this.value = valor;
        });
    }
});
</script>
HTML;
    }

    protected function procesaFormulario(array $datos): ?string
    {
        return null;
    }
}
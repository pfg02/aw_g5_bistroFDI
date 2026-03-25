<?php

abstract class Formulario
{
    protected string $formId;
    protected string $method;
    protected string $action;
    protected ?string $urlRedireccion;
    protected array $errores = [];

    public function __construct(string $formId, array $opciones = [])
    {
        $opciones = array_merge([
            'method' => 'POST',
            'action' => htmlspecialchars($_SERVER['REQUEST_URI']),
            'urlRedireccion' => null,
        ], $opciones);

        $this->formId = $formId;
        $this->method = $opciones['method'];
        $this->action = $opciones['action'];
        $this->urlRedireccion = $opciones['urlRedireccion'];
    }

    public function gestiona(): string
    {
        $datos = strcasecmp($this->method, 'GET') === 0 ? $_GET : $_POST;

        if (!$this->formularioEnviado($datos)) {
            return $this->generaFormulario();
        }

        $this->errores = [];
        $this->procesaFormulario($datos);

        if (!empty($this->errores)) {
            return $this->generaFormulario($datos);
        }

        if ($this->urlRedireccion !== null) {
            header('Location: ' . $this->urlRedireccion);
            exit;
        }

        return '';
    }

    protected function formularioEnviado(array $datos): bool
    {
        return isset($datos['formId']) && $datos['formId'] === $this->formId;
    }

    protected static function h(?string $valor): string
    {
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
    }

    protected static function mensajeError(array $errores, string $campo): string
    {
        if (!isset($errores[$campo])) {
            return '';
        }
        return '<p style="color:red;">' . self::h($errores[$campo]) . '</p>';
    }

    protected static function listaErroresGlobales(array $errores): string
    {
        $globales = array_filter(
            $errores,
            static fn($k) => is_int($k) || ctype_digit((string)$k),
            ARRAY_FILTER_USE_KEY
        );

        if (empty($globales)) {
            return '';
        }

        $html = '<ul style="color:red;">';
        foreach ($globales as $error) {
            $html .= '<li>' . self::h($error) . '</li>';
        }
        $html .= '</ul>';

        return $html;
    }

    abstract protected function generaCamposFormulario(array &$datos): string;
    abstract protected function procesaFormulario(array &$datos): void;

    protected function generaFormulario(array $datos = []): string
    {
        $campos = $this->generaCamposFormulario($datos);

        return <<<HTML
<form method="{$this->method}" action="{$this->action}">
    <input type="hidden" name="formId" value="{$this->formId}">
    {$campos}
</form>
HTML;
    }
}
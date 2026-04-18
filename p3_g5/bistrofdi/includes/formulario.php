<?php

abstract class Formulario
{
    private string $idForm;
    private string $method;
    private string $action;
    private ?string $classCss;
    private ?string $enctype;
    private ?string $urlRedireccion;

    protected array $errores;
    protected string $mensajeExito;

    public function __construct(string $idForm, array $opciones = [])
    {
        $this->idForm = $idForm;
        $this->method = $opciones['method'] ?? 'POST';
        $this->action = $opciones['action'] ?? '';
        $this->classCss = $opciones['class'] ?? null;
        $this->enctype = $opciones['enctype'] ?? null;
        $this->urlRedireccion = $opciones['urlRedireccion'] ?? null;

        $this->errores = [];
        $this->mensajeExito = '';
    }

    public function gestiona(): string
    {
        $datos = $this->method === 'GET' ? $_GET : $_POST;

        if (!$this->formularioEnviado($datos)) {
            return $this->generaFormulario();
        }

        $resultado = $this->procesaFormulario($datos);

        if (is_string($resultado) && $resultado !== '') {
            header("Location: $resultado");
            exit();
        }

        if (empty($this->errores) && $this->urlRedireccion) {
            header("Location: {$this->urlRedireccion}");
            exit();
        }

        if (empty($this->errores) && $this->mensajeExito !== '') {
            $datos = $this->getValoresTrasExito($datos);
        }

        return $this->generaFormulario($datos);
    }

    protected function formularioEnviado(array $datos): bool
    {
        return isset($datos['idFormulario']) && $datos['idFormulario'] === $this->idForm;
    }

    private function generaFormulario(array $datos = []): string
    {
        $classAtt = $this->classCss ? 'class="' . htmlspecialchars($this->classCss) . '"' : '';
        $enctypeAtt = $this->enctype ? 'enctype="' . htmlspecialchars($this->enctype) . '"' : '';

        $htmlCampos = $this->generaCamposFormulario($datos);
        $idForm = htmlspecialchars($this->idForm);
        $action = htmlspecialchars($this->action);
        $method = htmlspecialchars($this->method);

        return <<<HTML
<form method="$method" action="$action" $classAtt $enctypeAtt>
    <input type="hidden" name="idFormulario" value="$idForm">
    $htmlCampos
</form>
HTML;
    }

    protected static function generaListaErrores(array $errores): string
    {
        if (empty($errores)) {
            return '';
        }

        $html = '<div class="alerta-sistema alerta-error"><ul>';
        foreach ($errores as $error) {
            $html .= '<li>' . htmlspecialchars($error) . '</li>';
        }
        $html .= '</ul></div>';

        return $html;
    }

    public function getMensajeExito(): string
    {
        return $this->mensajeExito;
    }

    protected function getValoresTrasExito(array $datos): array
    {
        return $datos;
    }

    abstract protected function generaCamposFormulario(array $datos): string;

    /**
     * Devuelve:
     * - null si no hay redirección
     * - string con URL si hay que redirigir
     */
    abstract protected function procesaFormulario(array $datos): ?string;
}
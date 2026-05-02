<?php
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../negocio/UsuarioController.php';
require_once __DIR__ . '/../../formularios/formularioRegistro.php';

$controller = new UsuarioController();
$formulario = new FormularioRegistro($controller);
$htmlFormulario = $formulario->gestiona();

ob_start();
?>
<section class="f0-auth-wrap">
    <div class="f0-auth-card">
        <div class="f0-auth-body">
            <h1 class="f0-auth-title">Registrarse</h1>
            <div class="f0-auth-divider"></div>

            <?= $htmlFormulario ?>

            <div class="f0-auth-switch">
                <p><a href="login.php">Ir a iniciar sesión</a></p>
                <p><a href="../../../index.php">Volver al inicio</a></p>
            </div>
        </div>
    </div>
</section>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
const BASE_URL = '<?= BASE_URL ?>';

console.log('AJAX registro inline cargado');
console.log('BASE_URL:', BASE_URL);

$(document).ready(function () {
    console.log('jQuery preparado');

    function mostrarError(campo, mensaje) {
        console.log('ERROR:', campo, mensaje);

        $('#error-' + campo)
            .text(mensaje)
            .removeClass('mensaje-ok')
            .addClass('mensaje-error');

        $('#' + campo)
            .removeClass('input-ok')
            .addClass('input-error');
    }

    function mostrarOk(campo, mensaje) {
        console.log('OK:', campo, mensaje);

        $('#error-' + campo)
            .text(mensaje)
            .removeClass('mensaje-error')
            .addClass('mensaje-ok');

        $('#' + campo)
            .removeClass('input-error')
            .addClass('input-ok');
    }

    function validarCampoAjax(campo, valor) {
        console.log('Validando por AJAX:', campo, valor);

        $.ajax({
            url: BASE_URL + '/includes/acciones/auth/ajax_validar_registro.php',
            type: 'POST',
            dataType: 'json',
            data: {
                campo: campo,
                valor: valor
            },
            success: function (respuesta) {
                console.log('Respuesta AJAX:', respuesta);

                if (respuesta.ok) {
                    mostrarOk(campo, respuesta.mensaje);
                } else {
                    mostrarError(campo, respuesta.mensaje);
                }
            },
            error: function (xhr) {
                console.log('Error AJAX:');
                console.log(xhr.responseText);

                mostrarError(campo, 'No se ha podido validar el campo.');
            }
        });
    }

    $(document).on('blur', '#nombre_usuario', function () {
        const nombreUsuario = $(this).val().trim();

        console.log('Blur nombre_usuario:', nombreUsuario);

        if (nombreUsuario === '') {
            mostrarError('nombre_usuario', 'El nombre de usuario es obligatorio.');
            return;
        }

        if (nombreUsuario.length < 3) {
            mostrarError('nombre_usuario', 'El nombre de usuario debe tener al menos 3 caracteres.');
            return;
        }

        validarCampoAjax('nombre_usuario', nombreUsuario);
    });

    $(document).on('blur', '#email', function () {
        const email = $(this).val().trim();

        console.log('Blur email:', email);

        if (email === '') {
            mostrarError('email', 'El email es obligatorio.');
            return;
        }

        validarCampoAjax('email', email);
    });

    $(document).on('input', '#password', function () {
        const password = $(this).val();

        if (password.length > 0 && password.length < 6) {
            mostrarError('password', 'La contraseña debe tener al menos 6 caracteres.');
        } else if (password.length >= 6) {
            mostrarOk('password', 'Contraseña válida.');
        }
    });

    $(document).on('input', '#password2', function () {
        const password = $('#password').val();
        const password2 = $(this).val();

        if (password2.length > 0 && password !== password2) {
            mostrarError('password2', 'Las contraseñas no coinciden.');
        } else if (password2.length > 0 && password === password2) {
            mostrarOk('password2', 'Las contraseñas coinciden.');
        }
    });
});
</script>

<?php
$contenidoPrincipal = ob_get_clean();
$tituloPagina = 'Registro - Bistro FDI';
$bodyClass = 'f0-body';

require __DIR__ . '/../partials/plantilla.php';
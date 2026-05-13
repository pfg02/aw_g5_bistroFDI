$(document).ready(function () {
    let emailValido = false;
    let nombreUsuarioValido = false;

    function mostrarError(campo, mensaje) {
        $('#error-' + campo)
            .text(mensaje)
            .removeClass('mensaje-ok')
            .addClass('mensaje-error');

        $('#' + campo)
            .removeClass('input-ok')
            .addClass('input-error');
    }

    function mostrarOk(campo, mensaje) {
        $('#error-' + campo)
            .text(mensaje)
            .removeClass('mensaje-error')
            .addClass('mensaje-ok');

        $('#' + campo)
            .removeClass('input-error')
            .addClass('input-ok');
    }

    function limpiarMensaje(campo) {
        $('#error-' + campo)
            .text('')
            .removeClass('mensaje-error mensaje-ok');

        $('#' + campo)
            .removeClass('input-error input-ok');
    }

    function validarCampoAjax(campo, valor) {
        $.ajax({
            url: BASE_URL + '/includes/acciones/auth/ajax_validar_registro.php',
            type: 'POST',
            dataType: 'json',
            data: {
                campo: campo,
                valor: valor
            },
            success: function (respuesta) {
                if (respuesta.ok) {
                    mostrarOk(campo, respuesta.mensaje);

                    if (campo === 'email') {
                        emailValido = true;
                    }

                    if (campo === 'nombre_usuario') {
                        nombreUsuarioValido = true;
                    }
                } else {
                    mostrarError(campo, respuesta.mensaje);

                    if (campo === 'email') {
                        emailValido = false;
                    }

                    if (campo === 'nombre_usuario') {
                        nombreUsuarioValido = false;
                    }
                }
            },
            error: function () {
                mostrarError(campo, 'No se ha podido validar el campo.');

                if (campo === 'email') {
                    emailValido = false;
                }

                if (campo === 'nombre_usuario') {
                    nombreUsuarioValido = false;
                }
            }
        });
    }

    $('#nombre_usuario').on('blur', function () {
        const nombreUsuario = $(this).val().trim();
        nombreUsuarioValido = false;

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

    $('#email').on('blur', function () {
        const email = $(this).val().trim();
        emailValido = false;

        if (email === '') {
            mostrarError('email', 'El email es obligatorio.');
            return;
        }

        validarCampoAjax('email', email);
    });

    $('#nombre_usuario').on('input', function () {
        nombreUsuarioValido = false;
        limpiarMensaje('nombre_usuario');
    });

    $('#email').on('input', function () {
        emailValido = false;
        limpiarMensaje('email');
    });

    $('#password').on('input', function () {
        const password = $(this).val();

        if (password === '') {
            limpiarMensaje('password');
            return;
        }

        if (password.length < 6) {
            mostrarError('password', 'La contraseña debe tener al menos 6 caracteres.');
        } else {
            mostrarOk('password', 'Contraseña válida.');
        }

        const password2 = $('#password2').val();

        if (password2 !== '') {
            if (password !== password2) {
                mostrarError('password2', 'Las contraseñas no coinciden.');
            } else {
                mostrarOk('password2', 'Las contraseñas coinciden.');
            }
        }
    });

    $('#password2').on('input', function () {
        const password = $('#password').val();
        const password2 = $(this).val();

        if (password2 === '') {
            limpiarMensaje('password2');
            return;
        }

        if (password !== password2) {
            mostrarError('password2', 'Las contraseñas no coinciden.');
        } else {
            mostrarOk('password2', 'Las contraseñas coinciden.');
        }
    });

    $('.f0-form').on('submit', function (e) {
        let hayErrores = false;

        const email = $('#email').val().trim();
        const nombreUsuario = $('#nombre_usuario').val().trim();
        const password = $('#password').val();
        const password2 = $('#password2').val();

        if (nombreUsuario === '') {
            mostrarError('nombre_usuario', 'El nombre de usuario es obligatorio.');
            hayErrores = true;
        }

        if (nombreUsuario !== '' && nombreUsuario.length < 3) {
            mostrarError('nombre_usuario', 'El nombre de usuario debe tener al menos 3 caracteres.');
            hayErrores = true;
        }

        if (email === '') {
            mostrarError('email', 'El email es obligatorio.');
            hayErrores = true;
        }

        if (password.length < 6) {
            mostrarError('password', 'La contraseña debe tener al menos 6 caracteres.');
            hayErrores = true;
        }

        if (password2 === '') {
            mostrarError('password2', 'Debes repetir la contraseña.');
            hayErrores = true;
        }

        if (password !== password2) {
            mostrarError('password2', 'Las contraseñas no coinciden.');
            hayErrores = true;
        }

        if (!emailValido) {
            mostrarError('email', 'Valida el email antes de enviar.');
            hayErrores = true;
        }

        if (!nombreUsuarioValido) {
            mostrarError('nombre_usuario', 'Valida el nombre de usuario antes de enviar.');
            hayErrores = true;
        }

        if (hayErrores) {
            e.preventDefault();
        }
    });
});
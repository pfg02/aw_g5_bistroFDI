$(document).ready(function() {


    $(".btn-toggle-vip").click(function(e) {
        e.preventDefault(); // Evita que el botón envíe formularios por error
        
        let boton = $(this);
        let idUsuario = boton.data("id");
        let estadoVipActual = boton.data("vip");
        
        // Calculamos el nuevo estado (si era 0 pasa a 1, si era 1 pasa a 0)
        let nuevoEstado = (estadoVipActual === 1) ? 0 : 1;

        // Petición AJAX por POST
        $.post("../../../includes/acciones/auth/ajax_vip.php", { id: idUsuario, vip: nuevoEstado }, function(respuesta) {
            if (respuesta.trim() === "ok") {
                // Actualizamos el botón visualmente sin recargar la página
                boton.data("vip", nuevoEstado);
                if (nuevoEstado === 1) {
                    boton.html("Quitar VIP ⭐");
                    boton.removeClass("f0-btn").addClass("f0-btn-danger");
                } else {
                    boton.html("Hacer VIP");
                    boton.removeClass("f0-btn-danger").addClass("f0-btn");
                }
            } else {
                alert("Error al actualizar el estado VIP del usuario.");
            }
        });
    });
});
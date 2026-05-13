$(document).ready(function() {
    // Usamos .on("click") por si cargas productos dinámicamente
    $(document).on("click", ".btn-favorito", function(e) {
        e.preventDefault(); 
        
        let boton = $(this);
        let idProd = boton.data("id");
        
        // Petición AJAX
        $.post("../../../includes/acciones/carrito/ajax_favorito.php", { id_producto: idProd }, function(respuesta) {
            let res = respuesta.trim();
            
            if (res === "anadido") {
                // Se guardó en la BD, lo ponemos rojo
                boton.addClass("activo");
            } else if (res === "quitado") {
                // Se borró de la BD, le quitamos el rojo
                boton.removeClass("activo");
            } else {
                alert("Error al guardar favorito. Inténtalo de nuevo.");
            }
        }).fail(function() {
            alert("Error crítico de conexión.");
        });
    });
});
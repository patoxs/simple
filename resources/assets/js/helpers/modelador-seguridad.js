function seleccionarSeguridad(procesoId) {
    $("#modal").load("/backend/seguridad/ajax_seleccionar/" + procesoId);
    $("#modal").modal();
    return false;
}
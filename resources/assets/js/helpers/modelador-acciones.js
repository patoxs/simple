function seleccionarAccion(procesoId) {
    $("#modal").load("/backend/acciones/ajax_seleccionar/" + procesoId);
    $("#modal").modal();
    return false;
}
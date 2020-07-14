<div class="modal-dialog" role="document">
    <div class="modal-content">
		<div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Editar vencimiento</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
		<div class="modal-body">
			<form id="formEditarVencimiento" method='POST' class='ajaxForm'
				action="<?= url('backend/seguimiento/editar_vencimiento_form/' . $etapa->id) ?>">
				{{csrf_field()}}
				<div class='validacion'></div>
				<label>Fecha de Vencimiento</label> <input class='datetimepicker form-control'
					name='vencimiento' id="vencimiento" type='date'
					value="<?= date('d-m-Y',  strtotime($etapa->vencimiento_at)) ?>"
					required />
			<label>Indique la razón del cambio de fecha:</label>
			<textarea class="form-control col-12" name='descripcion' type='text' required></textarea>
			</form>
			
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal">Cerrar</button>
			<a href="#"
				onclick="javascript:$('#formEditarVencimiento').submit();
		        return false;"
				class="btn btn-primary">Guardar</a>
		</div>
	</div>
</div>
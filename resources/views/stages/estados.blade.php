<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Historial de estados</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">Estado</th>
                        <th scope="col">Etapa</th>
                        <th scope="col">Descripción</th>
                        <th scope="col">Mensaje</th>
                        <th scope="col">Número de Expediente</th>
                        <th scope="col">Fecha Notificación</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($historial as $h)
                        <tr>
                            <td>{{!isset($h->status) ? '' : $h->status}}</td>
                            <td>{{!isset($h->stage) ? '' : $h->stage}}</td>
                            <td>{{!isset($h->description) ? '' : $h->description}}</td>
                            <td>{{!isset($h->message) ? '' : $h->message}}</td>
                            <td>{{!isset($h->service_application_id) ? '' : $h->service_application_id}}</td>
                            <td>{{$h->created_at}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="modal-footer">
            <a class="btn btn-light closeModal" data-dismiss="modal">Cerrar</a>
        </div>
    </div>
</div>
@extends('layouts.procedure')

@section('content')
    <div class="row">
        <div class="col-xs-12 col-md-8">
            <h2>Bandeja de Entrada</h2>
        </div>
        <div class="col-xs-12 col-md-4">
            <!--buscador-->
            <form class="form-search" method="GET" action="">
                <div class="search-form form-inline float-right">
                    <div class="input-group mb-3">
                        <input class="search-form_input form-control" placeholder="Escribe aquí lo que deseas buscar"
                               type="text"
                               name="query"
                               value="{{ $query }}">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="material-icons">search</i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-md-12">
            <div class="table-responsive">

                @if($etapas)
                    <table class="table table-condensed table-hover">
                        <thead>
                        <tr>
                            <th class="text-center"></th>
                            <th class="text-center"><a href="{{ getUrlSortUnassigned($request, 'numero') }}">Nro.</a></th>
                            <th class="text-center">Ref.</th>
                            <th class="text-center">Nombre</th>
                            <th class="text-center"><a href="{{ getUrlSortUnassigned($request, 'etapa') }}">Etapa</a></th>
                            <th class="text-center"><a href="{{ getUrlSortUnassigned($request, 'ingreso') }}">Ingreso</a></th>
                            <th class="text-center"><a href="{{ getUrlSortUnassigned($request, 'modificacion') }}">Modificación</a></th>
                            <th class="text-center"><a href="{{ getUrlSortUnassigned($request, 'vencimiento') }}">Venc.</a></th>
                            <th class="text-center">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($etapas as $e)
                            <tr {!! getPrevisualization($e) ? 'data-toggle="popover" data-html="true" data-title="<h4>Previsualización</h4>" data-content="' . htmlspecialchars(getPrevisualization($e)) . '" data-trigger="hover" data-placement="bottom"' : '' !!}>
                                <td class="text-center">
                                    @if($cuenta->descarga_masiva && $e->tramite->files->count() > 0)
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" class="checkbox1" name="select[]" value="{{ $e->id }}">
                                        </label>
                                    </div>
                                    @endif
                                </td> 
                                <td class="text-center "> {{ $e->tramite->id }}</td>
                                <td class="text-center ">{{ getValorDatoSeguimiento($e, 'tramite_ref') }}</td>  
                                <td class="text-center ">{{ getValorDatoSeguimiento($e, 'tramite_descripcion') }}</td>               
                                <td class="text-center">{{ $e->tarea->nombre }}</td>                            
                                <td class="text-center ">{{ getDateFormat($e->tramite->created_at)}}</td>
                                <td class="text-center ">{{ getDateFormat($e->tramite->updated_at)}}</td>
                                <td class="text-center ">{{ $e->vencimiento_at ? getDateFormat($e->vencimiento_at, 'vencimiento') : 'N/A'}}</td>
                                <td class="text-center actions">
                                    <a href="{{ url('etapas/ejecutar/' . $e->id) }}" class="btn btn-sm btn-primary preventDoubleRequest">
                                        <i class="icon-edit icon-white"></i> Realizar
                                    </a>
                                    @if($cuenta->descarga_masiva && $e->tramite->files->count() > 0) 
                                        <a href="javascript:;" onclick="return descargarDocumentos({{ $e->tramite->id}});" class="btn btn btn-sm btn-success">
                                            <i class="icon-download icon-white"></i> Descargar
                                        </a>                               
                                   @endif
                                    @if(Auth::check() && Auth::user()->open_id && !is_null($e->tarea->proceso->eliminar_tramites) && $e->Tarea->Proceso->eliminar_tramites)
                                        <a href="#" onclick="return eliminarTramite({{$e->Tramite->id}});" class="btn btn-sm btn-danger preventDoubleRequest">
                                            <i class="icon-edit icon-red"></i> Borrar
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @if ($cuenta->descarga_masiva && hasFiles($etapas))
                <div class="pull-right">
                    <div class="checkbox">
                        <input type="hidden" id="tramites" name="tramites"/>
                        <label>
                            <input type="checkbox" id="select_all" name="select_all"/> Seleccionar todos
                            <a href="#" onclick="return descargarSeleccionados();" class="button preventDoubleRequest">Descargar
                                seleccionados</a>
                        </label>
                    </div>
                </div>
                @endif
                <p>
                    {{ $etapas->appends(Request::except('page'))->render("pagination::bootstrap-4")}}
                </p>
                @else
                <p>No hay trámites pendientes en su bandeja de entrada.</p>
                @endif
            </div>
        </div>
    </div>
    <div class="modal hide in" id="modal"></div>
@endsection
@push('script')
    <script>
        function descargarDocumentos(tramiteId) {
            $("#modal").load("/etapas/descargar/" + tramiteId);
            $("#modal").modal();
            $("#modal").css('display', 'block');

            $(".closeModal").click(function () {
                closeModal();
                console.log("test1");
            });

            $(".modal-backdrop").click(function () {
                closeModal();
                console.log("test2");
            });

            $(".modal-backdrop").click(function () {
                closeModal();
                console.log("test3");
            });

            return false;
        }

        $(document).ready(function () {
            $('#select_all').click(function (event) {
                var checked = [];
                $('#tramites').val();
                if (this.checked) {
                    $('.checkbox1').each(function () {
                        this.checked = true;
                    });
                } else {
                    $('.checkbox1').each(function () {
                        this.checked = false;
                    });
                }
                $('#tramites').val(checked);
            });
        });

        function closeModal() {
            $("#modal").removeClass("in");
            $(".modal-backdrop").remove();
            $('body').removeClass('modal-open');
            $('body').css('padding-right', '');
            $("#modal").hide();
        }

        function descargarSeleccionados() {
            var numberOfChecked = $('.checkbox1:checked').length;
            if (numberOfChecked == 0) {
                alert('Debe seleccionar al menos un trámite');
                return false;
            } else {
                var checked = [];
                $('.checkbox1').each(function () {
                    if ($(this).is(':checked')) {
                        checked.push(parseInt($(this).val()));
                    }
                });
                $('#tramites').val(checked);
                var tramites = $('#tramites').val();
                $("#modal").load("/etapas/descargar/" + tramites);
                $("#modal").modal();
                console.log("descargarSeleccionados.modal");
                return false;
            }
        }

        function eliminarTramite(tramiteId) {
            $("#modal").load("/tramites/eliminar/" + tramiteId);
            $("#modal").modal();
            $("#modal").css('display', 'block');

            $(".closeModal").click(function () {
                closeModal();
                console.log("test1");
            });

            $(".modal-backdrop").click(function () {
                closeModal();
                console.log("test2");
            });

            $(".modal-backdrop").click(function () {
                closeModal();
                console.log("test3");
            });

            return false;
        }
    </script>
@endpush
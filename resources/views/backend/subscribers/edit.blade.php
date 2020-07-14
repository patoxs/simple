@extends('layouts.backend')

@section('title', $title)

@section('content')
    <div class="container-fluid mt-3">

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('backend.procesos.index')}}">Listado de Procesos</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{$proceso->nombre}}</li>
            </ol>
        </nav>

        @include('backend.process.nav')

        <form id="plantillaForm" class="ajaxForm" method="POST"
              action="<?=route('backend.subscribers.edit_form', ($edit ? [$suscriptor->id] : ''))?>">
            {{csrf_field()}}
            <fieldset>
                @if(!$edit)
                    <legend> Regitrar suscriptores
                        <a onclick="ga('cuenta.send', 'event', 'Mejora_UX', 'Ayuda-Registrar-Suscriptor');" href="/ayuda/simple/backend/modelamiento-del-proceso/acciones.html#suscriptores"
                           target="_blank">
                            <i class="material-icons align-middle">help</i>
                        </a>
                    </legend>
                @endif
                @if($edit)
                    <legend> Editar suscriptores
                        <a onclick="ga('cuenta.send', 'event', 'Mejora_UX', 'Ayuda-Editar-Suscriptor');" href="/ayuda/simple/backend/modelamiento-del-proceso/acciones.html#suscriptores"
                           target="_blank">
                            <i class="material-icons">help</i>
                        </a>
                    </legend>
                @endif
                <div class="validacion"></div>
                @if(!$edit)
                    <input type="hidden" name="proceso_id" value="<?=$proceso->id?>"/>
                @endif
                <label>Nombre de la Institución</label>
                <input type="text" name="institucion" class="form-control col-2" value="<?=$edit ? $suscriptor->institucion : ''?>"/>
                <?= $suscriptor->displayFormSuscriptor($proceso->id) ?>
                <div class="form-actions">
                    <a class="btn btn-light" href="<?=route('backend.subscribers.list', [$proceso->id])?>">Cancelar</a>
                    <button class="btn btn-primary" value="Guardar" onclick="validateForm();" type="button">
                        Guardar
                    </button>
                </div>
            </fieldset>
        </form>
    </div>
    </div>
@endsection
@section('script')
    <script>
        function validateForm() {
            var resultR = isJsonR();
            if (resultR != '0') {
                $("#request").addClass('invalido');
                $("#resultRequest").text("Formato requerido / json");
            } else {
                $("#request").removeClass('invalido');
                $("#resultRequest").text("");
                javascript:$('#plantillaForm').submit();
            }
        }

        function isJsonR() {
            try {
                if ($("#request").val() != null && $("#request").val() != '') {
                    JSON.parse($("#request").val());
                }
            } catch (e) {
                return 1;
            }
            return 0;
        }
    </script>
@endsection
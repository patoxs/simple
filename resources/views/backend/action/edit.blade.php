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

        <form id="plantillaForm" enctype="multipart/form-data" method="POST" onsubmit="return"
              action="<?=route('backend.action.edit_form', ($edit ? [$accion->id] : ''))?>">
            {{csrf_field()}}
            <fieldset>
                <legend>
                    @if(!$edit)
                        Crear Acción
                    @endif
                    @if($edit)
                        @php
                            $tipo = $accion->tipo;
                        @endphp
                        Editar Acción
                    @endif
                    <?php if ($tipo == "enviar_correo") { ?>
                    <a onclick="ga('cuenta.send', 'event', 'Mejora_UX', 'Ayuda_Accion_enviar_correo');" href="/ayuda/simple/backend/modelamiento-del-proceso/acciones.html#enviar_correo"
                       target="_blank">
                        <i class="material-icons align-middle">help</i>
                    </a>
                    <?php } else if ($tipo == "webservice") { ?>
                    <a onclick="ga('cuenta.send', 'event', 'Mejora_UX', 'Ayuda_Accion_WS');" href="/ayuda/simple/backend/modelamiento-del-proceso/acciones.html#webservice"
                       target="_blank">
                        <i class="material-icons align-middle">help</i>
                    </a>
                    <?php } else if ($tipo == "variable") { ?>
                    <a onclick="ga('cuenta.send', 'event', 'Mejora_UX', 'Ayuda_accion_variable');" href="/ayuda/simple/backend/modelamiento-del-proceso/acciones.html#variable"
                       target="_blank">
                        <i class="material-icons align-middle">help</i>
                    </a>
                    <?php } else if ($tipo == "rest") { ?>
                    <a onclick="ga('cuenta.send', 'event', 'Mejora_UX', 'Ayuda_accion_ws_rest');" href="/ayuda/simple/backend/modelamiento-del-proceso/acciones.html#webservice-rest"
                       target="_blank">
                        <i class="material-icons align-middle">help</i>
                    </a>
                    <?php } else if ($tipo == "soap") { ?>
                    <a onclick="ga('cuenta.send', 'event', 'Mejora_UX', 'Ayuda_accion_ws_soap');" href="/ayuda/simple/backend/modelamiento-del-proceso/acciones.html#webservice-soap"
                       target="_blank">
                        <i class="material-icons align-middle">help</i>
                    </a>
                    <?php } else if ($tipo == "callback") { ?>
                    <a onclick="ga('cuenta.send', 'event', 'Mejora_UX', 'Ayuda_accion_callback');" href="/ayuda/simple/backend/modelamiento-del-proceso/acciones.html#callback"
                       target="_blank">
                        <i class="material-icons align-middle">help</i>
                    </a>
                    <?php } else if ($tipo == "webhook") { ?>
                    <a onclick="ga('cuenta.send', 'event', 'Mejora_UX', 'Ayuda_ accion_webhook');" href="/ayuda/simple/backend/modelamiento-del-proceso/acciones.html#webhook"
                       target="_blank">
                        <i class="material-icons align-middle">help</i>
                    </a>
                    <?php } else if ($tipo == "iniciar_tramite") { ?>
                    <a onclick="ga('cuenta.send', 'event', 'Mejora_UX', 'Ayuda_accion_iniciar_tramite');" href="/ayuda/simple/backend/modelamiento-del-proceso/acciones.html#iniciar-tramite"
                       target="_blank">
                        <i class="material-icons align-middle">help</i>
                    </a>
                    <?php } else if ($tipo == "continuar_tramite") { ?>
                    <a onclick="ga('cuenta.send', 'event', 'Mejora_UX', 'Ayuda_accion_continuar_tramite');" href="/ayuda/simple/backend/modelamiento-del-proceso/acciones.html#continuar-tramite"
                       target="_blank">
                        <i class="material-icons align-middle">help</i>
                    </a>
                    <?php } ?>
                </legend>
                <div class="validacion"></div>
                <?php if(!$edit):?>
                <input type="hidden" name="proceso_id" value="<?=$proceso->id?>"/>
                <input type="hidden" name="tipo" value="<?=$tipo?>"/>
                <?php endif; ?>
                <label>Nombre de la acción</label>
                <input type="text" name="nombre" class="form-control col-2" value="<?=$edit ? $accion->nombre : ''?>"/>
                <label>Tipo</label>
                <input type="text" class="form-control col-2" readonly value="<?=$edit ? $accion->tipo : $tipo?>"/>

                @php
                    Log::info("En view editar, tipo: " . $tipo);
                    $key = '';
                    ($tipo ? $key = $tipo : $key = $accion->tipo);
                    Log::info("En view editar, $key: " . $key);
                    if ($tipo == "rest" || $tipo == "soap" || $tipo == "callback" || $accion->tipo == "rest" || $accion->tipo == "soap" || $accion->tipo == "callback" || $accion->tipo == "iniciar_tramite" || $tipo == "iniciar_tramite" || $accion->tipo == "continuar_tramite" || $tipo == "continuar_tramite") {
                        echo $accion->displaySecurityForm($proceso->id);
                    } else if ($tipo == "webhook" || $accion->tipo == "webhook") {
                        echo $accion->displaySuscriptorForm($proceso->id);
                    } else if($tipo == "generar_documento" || $accion->tipo == "generar_documento") {
                        echo $accion->displayDocumentoForm($proceso->id);
                    } else {
                        echo $accion->displayForm();
                    }
                @endphp

                <div class="form-actions">
                    <a class="btn btn-light"
                       href="<?=route('backend.action.list', [$proceso->id])?>">Cancelar</a>
                    <button class="btn btn-primary" value="Guardar" type="button" onclick="validateForm();">
                        Guardar
                    </button>
                </div><br>
            </fieldset>
        </form>
    </div>
@endsection
@section('script')
    @php
        switch ($key) {
        case "rest":
        echo '<script src="' . asset('/js/helpers/accion_rest.js') . '"></script>';
        break;
        case "soap":
        echo '<script src="' . asset('/js/helpers/accion_soap.js') . '"></script>';
        break;
        case "callback":
        echo '<script src="' . asset('/js/helpers/accion_callback.js') . '"></script>';
        break;
        case "iniciar_tramite":
        echo '<script src="' . asset('/js/helpers/accion_tramite_simple.js') . '"></script>';
        break;
        default:
        echo '<script src="' . asset('/js/helpers/accion_otras.js') . '"></script>';
        break;
        }
    @endphp
@endsection
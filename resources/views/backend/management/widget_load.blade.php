<div class="front">
    <div class="cabecera">
        <h3><?=$widget->nombre?></h3>
        <a class="config" href="#" onclick="return widgetConfig(this)">
            <i class="material-icons white mini align-baseline">build</i>
        </a>
    </div>
    <div class="contenido">
        {!! $widget->display() !!}
    </div>
</div>
<div class="back">
    <form class="ajaxForm" method="POST" action="<?= route('backend.management.widget_config_form', $widget->id) ?>"
          data-onsuccess="widgetConfigOk">
        {{csrf_field()}}
        <div class="cabecera">
            <h3>Configuración</h3>
            <button type="submit" class="volver btn btn-light btn-sm">ok</button>
        </div>
        <div class="contenido">
            <div class="validacion"></div>
            <label>Nombre</label>
            <input type="text" class="form-control" name="nombre" value="<?=$widget->nombre?>"/>
            <?= $widget->displayForm() ?>

            <a class="btn btn-danger btn-block" href="<?=route('backend.management.widget_remove', [$widget->id])?>"
               style="margin-top: 100px;" onclick="return confirm('¿Esta seguro que desea eliminar este widget?')">
                <i class="material-icons">delete</i> Eliminar
            </a>
        </div>
    </form>
</div>
@section('script')
    @parent
    {!!$widget->getJavascript()!!}
@endsection
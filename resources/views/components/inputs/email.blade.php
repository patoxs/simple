<div class="form-group">

    @if(isset($no_input))
        <label for="{{$key}}" data-toggle="tooltip" data-placement="top" title="El email no puede ser modificado">
            {{$display_name ?? ucfirst($key)}}
        </label>
        <div class="form-group">
            <p class="form-control" style="background-color: #f2f2f2;">{{ $form->{$key} }}</p>
        </div>
    @else
        <label for="{{$key}}">{{$display_name ?? ucfirst($key)}}</label>
        <input type="email" name="{{$key}}" id="{{$key}}"
               class="form-control{{ $errors->has($key) ? ' is-invalid' : '' }}"
               value="{{ old($key, $form->{$key}) }}"
                {{isset($disabled) ? 'disabled' : ''}}>
    @endif
    @if ($errors->has($key))
        <div class="invalid-feedback">
            <strong>{{ $errors->first($key) }}</strong>
        </div>
    @endif

</div>

<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>

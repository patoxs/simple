<div class="form-group">
    <label for="{{$key}}">{{$display_name ?? ucfirst($key)}}</label>
    <input type="text" name="{{$key}}" id="{{$key}}"
           class="form-control{{ $errors->has($key) ? ' is-invalid' : '' }}"
           value="{{old($key, $form->{$key})}}">
    @if ($errors->has($key))
        <div class="invalid-feedback">
            <strong>{{ $errors->first($key) }}</strong>
        </div>
    @endif
</div>
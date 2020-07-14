<div class="form-group row">
    <div class="g-recaptcha" data-sitekey="{{env('RECAPTCHA_SITE_KEY')}}"></div>
    <input
            id="g-recaptcha-response"
            type="hidden"
            class="form-control{{ $errors->has('g-recaptcha-response') ? ' is-invalid' : '' }}"
    >
    @if ($errors->has('g-recaptcha-response'))
        <div class="invalid-feedback">
            <strong>{{ $errors->first('g-recaptcha-response') }}</strong>
        </div>
    @endif
</div>
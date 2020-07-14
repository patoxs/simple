@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-md-center mt-5">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{__('auth.register')}}</div>
                    <div class="card-body">
                        <form role="form" method="POST" action="{{ url('/register') }}">
                            {!! csrf_field() !!}

                            <div class="form-group row">
                                <label for="name" class="col-lg-4 col-form-label text-lg-right">{{__('auth.name')}}</label>

                                <div class="col-lg-6">
                                    <input
                                            type="text"
                                            class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"
                                            id="name"
                                            name="name"
                                            value="{{ old('name') }}"
                                            required
                                    >
                                    @if ($errors->has('name'))
                                        <div class="invalid-feedback">
                                            <strong>{{ $errors->first('name') }}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="email" class="col-lg-4 col-form-label text-lg-right">{{__('auth.email')}}</label>

                                <div class="col-lg-6">
                                    <input
                                            type="email"
                                            class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                            id="email"
                                            name="email"
                                            value="{{ old('email') }}"
                                            required
                                    >

                                    @if ($errors->has('email'))
                                        <div class="invalid-feedback">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="password" class="col-lg-4 col-form-label text-lg-right">{{__('auth.password')}}</label>

                                <div class="col-lg-6">
                                    <input
                                            type="password"
                                            class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                                            id="password"
                                            name="password"
                                            required
                                    >
                                    @if ($errors->has('password'))
                                        <div class="invalid-feedback">
                                            <strong>{{ $errors->first('password') }}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="password_confirmation" class="col-lg-4 col-form-label text-lg-right">
                                    {{__('auth.confirm_password')}}
                                </label>

                                <div class="col-lg-6">
                                    <input
                                            type="password"
                                            class="form-control{{ $errors->has('password_confirmation') ? ' is-invalid' : '' }}"
                                            id="password_confirmation"
                                            name="password_confirmation"
                                            required
                                    >
                                    @if ($errors->has('password_confirmation'))
                                        <div class="invalid-feedback">
                                            <strong>{{ $errors->first('password_confirmation') }}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-lg-6 offset-lg-4">
                                    <button type="submit" class="btn btn-primary">
                                        {{__('auth.register')}}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
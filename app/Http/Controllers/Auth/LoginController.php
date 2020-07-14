<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Rules\Captcha;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('guest')->except('logout');
    }


    /**
     * @return mixed
     */
    public function redirectToProvider(Request $request)
    {
        if ($request->has('redirect')) {
            $request->session()->put('claveunica_redirect', $request->input('redirect'));
        }
        //return Socialite::driver('claveunica')->scopes(['email', 'phone'])->redirect();
        //return Socialite::driver('claveunica')->scopes(['email'])->redirect();
        return Socialite::driver('claveunica')->redirect();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function handleProviderCallback(Request $request)
    {
        if ($request->has('error')) {
            return redirect(route('home'));
        }

        $user = Socialite::driver('claveunica')->user();
        $rut = (string) $user->run;
        $authUser = User::where('usuario', $rut)->where('open_id',1)->first();

        //Si no existe el usuario, se intenta crear,
        if (!$authUser) {
            $authUser = new User();
        }

        $authUser->rut = $user->run . '-' . $user->dv;
        //$authUser->dv = $user->dv;
        $authUser->nombres = $user->first_name;
        // $authUser->apellido_paterno = $user->last_name;
        $authUser->apellido_paterno = isset($user->primer_apellido) ? $user->primer_apellido : '';
        $authUser->apellido_materno = isset($user->segundo_apellido) ? $user->segundo_apellido : '';
        $authUser->usuario = $user->run;
        $authUser->email = is_null($user->email) ? '' : $user->email;
        $authUser->registrado = 1;
        $authUser->open_id = 1;
        $authUser->salt = '';
        //$authUser->phone = $user->phone;
        //$authUser->access_token = $user->token;
        //$authUser->refresh_token = $user->refreshToken;
        $authUser->save();

        Auth::login($authUser, true);

        // verificamos si existe un redirect en la session
        if ($request->session()->has('claveunica_redirect')) {

            // almacenamos en una variable auxiliar el redirect, para luego eliminarlo y realizar el redirect.
            $redirect = $request->session()->get('claveunica_redirect');
            $request->session()->forget('claveunica_redirect');

            return redirect($redirect);

        }

        return redirect('/');
    }

    /**
     * @param Request $request
     */
    protected function validateLogin(Request $request)
    {
        $validations = [
            $this->username() => 'required|string',
            'password' => 'required|string'
        ];

        if ($request->has('g-recaptcha-response')) {
            $validations['g-recaptcha-response'] = ['required', new Captcha];
        }

        $request->validate($validations);
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return redirect('/');
    }

    protected function authenticated(Request $request, $user)
    {
        if ($request->session()->has('redirect')) {
            $redirect = $request->session()->get('redirect');
            return redirect($redirect);
        }

    }

    public function showLoginForm(Request $request)
    {        
        $data = \Cuenta::configSegunDominio();

        if ($request->has('redirect')) {
            $request->session()->put('redirect', $request->input('redirect'));
        }
        return view('auth.login',$data);
    }

    public function logout_get(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return redirect('/');
    }
}

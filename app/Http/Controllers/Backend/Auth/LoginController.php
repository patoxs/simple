<?php

namespace App\Http\Controllers\Backend\Auth;

use App\Http\Controllers\Controller;
use App\Rules\Captcha;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
    protected $redirectTo = '/backend';

    // public function credentials(Request $request){

    //     if(\Request::server('HTTP_HOST') ==  env('APP_MAIN_DOMAIN')) {
    //        return  ['password' => $request->password, 'email'=> $request->email];
    //     }

    //     $http_post = explode('.', \Request::server('HTTP_HOST'));
    //     if(sizeof($http_post)<2){
    //         return redirect()->route('login');
    //     }
    //     $account_name = $http_post[0];
    //     if(is_null($account_name)||empty($account_name)){
    //         return redirect()->route('login');
    //     }
    //     $account = \Doctrine_Query::create()
    //         ->select('c.id')
    //         ->from('Cuenta c')
    //         ->where('c.nombre = ?', $account_name)
    //         ->fetchOne();
    //     if (!$account) {
    //         abort(404);
    //     }
        
    //     return ['cuenta_id'=> $account->id, 'password' => $request->password, 'email'=> $request->email];
    // }


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest:usuario_backend')->except('logout');
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('usuario_backend');
    }


    public function showLoginForm()
    {
        $data = \Cuenta::configSegunDominio();

        return view('backend.auth.login', $data);
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request $request
     * @return void
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

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return redirect()->route('backend.login');
    }
}

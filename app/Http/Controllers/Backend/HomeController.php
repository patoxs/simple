<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:usuario_backend');
    }

    public function index()
    {
        return (new ManagementController)->index();
    }

    public function terminos(Request $request)
    {
        $user = $request->user();

        if ($user->acepta_terminos) {
            return redirect('/terminos-y-condiciones');
        }

        $acepto_terminos = $request->input('acepto_terminos', false);

        if ($request->isMethod('post')) {
            if ($acepto_terminos) {
                $user->acepta_terminos = true;
                $user->fecha_aceptacion_terminos = Carbon::now();
                $user->save();

                $request->session()->flash('success', '¡Se aceptaron Los Términos y Condiciones con éxito!');

                return redirect('/backend');
            }

            return redirect('/backend/terminos-notificacion');
        }

        return view('terminos.backend_view', [
            'acepto_terminos' => $acepto_terminos
        ]);
    }

    public function terminosNotificacion(Request $request)
    {
        return view('terminos.not_accepted');
    }
}

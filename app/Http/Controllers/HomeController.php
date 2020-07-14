<?php

namespace App\Http\Controllers;

use App\Helpers\Doctrine;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Cuenta;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct()
    {
        #if user not logged, create new user and auto login this new user.
        //$this->middleware('auth_user');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        if(session()->has('redirect_url')){
            return redirect()->away(session()->get('redirect_url'));
        }

        $user_id = 1;

        if (Auth::check()) {
            $user_id = Auth::user()->id;
        }

        $procesos = Doctrine::getTable('Proceso')
            ->findProcesosDisponiblesParaIniciar($user_id, \Cuenta::cuentaSegunDominio(), 'nombre', 'asc');

        $cate = Doctrine::getTable('Categoria')->findAll();

        $cat_ids = array();
        $num_destacados = 0;
        $num_otros = 0;
        foreach ($procesos as $key => &$p) {
            /* if (strlen($p->nombre) > 45 ) {
                $p->nombre = substr($p->nombre, 0, 40) . '...';
            } */
            if ($p->destacado) {
                $num_destacados++;
            } else {
                $num_otros++;
            }
            array_push($cat_ids, $p->categoria_id);
        }

        $categorias = array();
        foreach ($cate as $key => $c) {
            if (in_array($c->id, $cat_ids)) {
                array_push($categorias, $c);
            }
        }

        $data = \Cuenta::configSegunDominio();

        $data['title'] = 'Home';
        $data['num_destacados'] = $num_destacados;
        $data['num_otros'] = $num_otros;
        $data['procesos'] = $procesos;
        $data['categorias'] = $categorias;
        $data['sidebar'] = 'disponibles';

        if (Auth::check() && Auth::user()->registrado) {
            return view('home.user_index', $data);
        } else {
            return view('home', $data);
        }
    }

    /**
     * @param $categoria_id
     */
    public function procesos($categoria_id)
    {

        $procesos = Doctrine::getTable('Proceso')->findProcesosDisponiblesParaIniciarByCategoria(Auth::user()->id, $categoria_id, Cuenta::cuentaSegunDominio(), 'nombre', 'asc');
        $categoria = Doctrine::getTable('Categoria')->find($categoria_id);

        $data = \Cuenta::configSegunDominio();
        
        $data['procesos'] = $procesos;
        $data['categoria'] = $categoria;
        $data['sidebar'] = 'categorias';

        if (Auth::check() && Auth::user()->registrado) {
            return view('home.user_index', $data);
        } else {
            return view('home', $data);
        }
    }

    public function terminosFront(Request $reuest)
    {
        return view('terminos.front_view');
    }

}

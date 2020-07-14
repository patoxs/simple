<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Helpers\Doctrine;
use Widget;

class ManagementController extends Controller
{
    public function index()
    {
        //$data['widgets'] = Auth::user()->Cuenta->Widgets;
        $data['widgets'] = Doctrine::getTable('Widget')->findByCuentaId(Auth::user()->cuenta_id);
        $data['title'] = 'Portada';

        return view('backend.home', $data);
    }

    public function widget_create($tipo)
    {
        $widget = new Widget();
        $widget->nombre = 'Nuevo widget';
        $widget->tipo = $tipo;
        $widget->cuenta_id = Auth::user()->cuenta_id;
        $widget->posicion = 0;
        $widget->save();

        return redirect($_SERVER['HTTP_REFERER']);
    }

    public function widget_change_positions(Request $request)
    {
        $cuenta = Auth::user()->Cuenta;

        $json = $request->input('posiciones');
        $cuenta->updatePosicionesWidgetsFromJSON($json);
    }

    public function widget_load($widget_id)
    {
        $widget = Doctrine::getTable('Widget')->find($widget_id);

        if (Auth::user()->cuenta_id != $widget->cuenta_id) {
            echo 'Usuario no tiene permisos para ver este widget';
            exit;
        }

        $data['widget'] = $widget;

        return view('backend.management.widget_load', $data);
    }

    public function widget_config_form(Request $request, $widget_id)
    {
        $widget = Doctrine::getTable('Widget')->find($widget_id);;

        if (Auth::user()->cuenta_id != $widget->cuenta_id) {
            echo 'Usuario no tiene permisos para ver este widget';
            exit;
        }

        $request->validate(['nombre' => 'required']);
        $widget->nombre = $request->input('nombre');
        $widget->config = $request->input('config');
        $widget->save();

        return response()->json(['validacion' => true]);
    }

    public function widget_remove($widget_id)
    {
        $widget = Doctrine::getTable('Widget')->find($widget_id);

        if (Auth::user()->cuenta_id != $widget->cuenta_id) {
            echo 'Usuario no tiene permisos';
            exit;
        }

        $widget->delete();

        return redirect($_SERVER['HTTP_REFERER']);
    }

}

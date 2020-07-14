<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\UsuarioManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use Exception;


class UserManagerController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $users_manager= DB::table('usuario_manager')->get();

        $data['users_manager'] = $users_manager;        
        $data['title'] = 'Mantenedor Usuarios Manager';
        $data['content'] = view('manager.users_manager.index', $data);

        return view('layouts.manager.app', $data);
    }


    /**
     * @param null $id_user
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id_user = null)
    {
        if ($id_user)
            $usuario = UsuarioManager::find($id_user);
        else
            $usuario = new UsuarioManager();

        $data['usuario'] = $usuario;
        $data['title'] = property_exists($usuario, 'id') ? 'Editar' : 'Crear Usuario Manager';
        $data['content'] = view('manager.users_manager.edit', $data);

        return view('layouts.manager.app', $data);
    }


    /**
     * @param Request $request
     * @param null $id_user
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function edit_form(Request $request, $id_user = null)
    {
        if ($id_user)
            $usuario = UsuarioManager::find($id_user);
         else
            $usuario = new UsuarioManager();

        if (!$usuario || $request->has('password')) {
            $validations['password'] = 'required|min:11|confirmed';
        }
       


        $respuesta = new \stdClass();
        $usuario->nombre = $request->input('nombre');
        $usuario->apellidos = $request->input('apellidos');
        $usuario->usuario = $request->input('usuario');
        $usuario->email = $request->input('email');
        $clave1 = $request->input('password');
        $clave2 = $request->input('password_confirmation');
        if($clave1 == $clave2){
         $usuario->password = $clave1;
            $usuario->password = Hash::make($request->input('password'));
            } 

        $usuario->save();
        $request->session()->flash('success', 'Usuario Manager guardado con éxito.');
        $respuesta->validacion = true;
        $respuesta->redirect = url('manager/usermanager');

        return response()->json($respuesta);
    }

     /**
     * @param Request $request
     * @param $id_user
     */
    public function delete(Request $request, $id_user){
        $usuario = UsuarioManager::find($id_user);
        $usuario->delete();

        $request->session()->flash('success', 'Usuario Manager eliminado con éxito.');
        return redirect('manager/usermanager');
    }


}

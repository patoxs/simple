<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Doctrine;
use Illuminate\Support\Facades\Hash;
use App\Models\UsuarioBackend;

class UsersController extends Controller
{

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $data['usuarios'] = Doctrine::getTable('UsuarioBackend')->findAll();

        $data['title'] = 'Usuarios Backend';
        $data['content'] = view('manager.users.index', $data);

        return view('layouts.manager.app', $data);
    }

    /**
     * @param null $usuario_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($usuario_id = null)
    {
        if ($usuario_id)
            $usuario = UsuarioBackend::find($usuario_id);
        else
            $usuario = new UsuarioBackend();


        $data['usuario'] = $usuario;
        $data['cuentas'] = Doctrine::getTable('Cuenta')->findAll();

        $data['title'] = property_exists($usuario, 'id') ? 'Editar' : 'Crear';
        $data['content'] = view('manager.users.edit', $data);

        return view('layouts.manager.app', $data);
    }

    /**
     * @param Request $request
     * @param null $usuario_id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function edit_form(Request $request, $usuario_id = null)
    {
        if ($usuario_id)
            $usuario = UsuarioBackend::find($usuario_id);
        else
            $usuario = new UsuarioBackend();

        $validations = [
            'email' => 'required|email',
            'nombre' => 'required',
            'apellidos' => 'required',
            'cuenta_id' => 'required',
            'rol' => 'required',
        ];

        $messages = [
            'email.required' => 'El campo Correo Electrónico es obligatorio',
            'nombre.required' => 'El campo Nombre es obligatorio',
            'apellidos.required' => 'El campo Apellidos es obligatorio',
            'cuenta_id.required' => 'El campo Cuenta es obligatorio',
            'rol.required' => 'El campo Rol es obligatorio',
        ];

        if (!$usuario->id || $request->has('password')) {
            $validations['password'] = 'required|min:6|confirmed';
        }

        $respuesta = new \stdClass();
        $usuario->email = $request->input('email');
        $usuario->nombre = $request->input('nombre');
        $usuario->apellidos = $request->input('apellidos');
        $usuario->cuenta_id = $request->input('cuenta_id');
        $usuario->rol = implode(",", $request->input('rol'));

        if ($request->input('password')) {
            $usuario->password = Hash::make($request->input('password'));
        }

        $usuario->save();

        $request->session()->flash('success', 'Usuario guardado con éxito.');
        $respuesta->validacion = true;
        $respuesta->redirect = url('manager/usuarios');

        return response()->json($respuesta);
    }

    /**
     * @param Request $request
     * @param $usuario_id
     */
    public function delete(Request $request, $usuario_id)
    {
        $usuario = UsuarioBackend::find($usuario_id);
        if (!is_null($usuario)) {
            $usuario->delete();

        }

        $request->session()->flash('success', 'Usuario eliminado con éxito.');
        return redirect('manager/usuarios');
    }

}

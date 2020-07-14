<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\Doctrine;
use App\Helpers\FileUploader;
use App\Models\Cuenta;
use App\Models\GrupoUsuarios;
use App\Models\UsuarioBackend;
use App\Rules\SafetyPassword;
use App\User;
use App\Models\FirmaElectronica;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Categoria;
use App\Models\Documento;
use DB;

class ConfigurationController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveMyAccount(Request $request)
    {
        if ($request->has('password') && !empty($request->input('password'))) {
            $this->validate($request, ['password' => 'required|confirmed|min:6']);

            UsuarioBackend::whereId(Auth::user()->id)
                ->update(['password' => Hash::make($request->input('password'))]);

            $request->session()->flash('status', 'Contraseña modificada con éxito');
        }


        return redirect()->route('backend.cuentas');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function mySite()
    {
        $data = Auth::user()->Cuenta;

        return view('backend.configuration.my_site.index', compact('data'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveMySite(Request $request)
    {
        $customMessages = [];
        $validations = ['name_large' => 'required|max:256'];
        $contacto_email = $request->input('contacto_email', null);
        $contacto_link = $request->input('contacto_link', null);

        if ($contacto_email && trim($contacto_email) != '') {
            $validations['contacto_email'] = 'email';
            $customMessages['email'] = 'El campo email de contacto no es válido.';
        }

        if ($contacto_link && trim($contacto_link) != '') {
            $validations['contacto_link'] = 'url';
            $customMessages['url'] = 'El campo link de contacto no es válido.';
        }

        $this->validate($request, $validations, $customMessages);

        $data = Cuenta::find(Auth::user()->cuenta_id);
        $data->nombre_largo = $request->input('name_large');
        $data->mensaje = is_null($request->input('message')) ? '' : $request->input('message', '');
        $data->descarga_masiva = $request->has('massive_download') ? 1 : 0;
        $data->logo = $request->input('logo');
        $data->logof = $request->input('logof');
        $data->favicon = $request->input('favicon');
        $data->setMetadata('contacto_email', $contacto_email);
        $data->setMetadata('contacto_link', $contacto_link);
        $data->save();

        $request->session()->flash('status', 'Cuenta modificada con éxito');

        return redirect()->route('backend.configuration.my_site');
    }

    /**
     * @param string $plantilla_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Exception
     */
    public function templates($plantilla_id = '')
    {
        $cuenta_id = Auth::user()->cuenta_id;

        if ($plantilla_id != '') {
            if ($plantilla_id != 1) {
                $cuentahasconfig = Doctrine::getTable('CuentaHasConfig')->findOneByIdparAndCuentaId(1, $cuenta_id);
                if ($cuentahasconfig == FALSE) {
                    $ctahascfg = new \CuentaHasConfig();
                    $ctahascfg->idpar = 1;
                    $ctahascfg->config_id = $plantilla_id;
                    $ctahascfg->cuenta_id = $cuenta_id;
                    $ctahascfg->save();
                } else {
                    $cuentahasconfig->config_id = $plantilla_id;
                    $cuentahasconfig->cuenta_id = $cuenta_id;
                    $cuentahasconfig->save();
                }
            } else {
                $cuentahasconfig = Doctrine::getTable('CuentaHasConfig')->findByIdparAndCuentaId(1, $cuenta_id);
                $cuentahasconfig->delete();
            }

        }
        $data['config_id'] = $plantilla_id;
        $data['config'] = Doctrine::getTable('Config')->findByIdparAndCuentaIdOrCuentaId(1, $cuenta_id, 0);

        return view('backend.configuration.template.index', $data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function storeTemplate(Request $request)
    {
        $this->validate($request, [
            'nombre_visible' => 'required',
            'nombre_plantilla' => 'required',
        ]);

        $existe = Doctrine::getTable('Config')
            ->findOneByIdparAndCuentaIdAndNombre(1, Auth::user()->cuenta_id, $request->input('nombre_plantilla'));
        if (!$existe) {
            $plantilla = new \Config();
            $plantilla->idpar = 1;
            $plantilla->cuenta_id = Auth::user()->cuenta_id;
            $plantilla->endpoint = 'plantilla';
            $plantilla->nombre_visible = $request->input('nombre_visible');
            $plantilla->nombre = $request->input('nombre_plantilla');

            $plantilla->save();
        } else {
            $existe->nombre_visible = $request->input('nombre_visible');
            $existe->nombre = $request->input('nombre_plantilla');
            $existe->save();
        }

        return redirect()->route('backend.configuration.template');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addTemplates()
    {
        $data['form'] = Auth::user()->Cuenta;
        $data['edit'] = true;

        return view('backend.configuration.template.edit', $data);
    }

    /**
     * @param $plantilla_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteTemplate($plantilla_id)
    {
        $cuenta_id = Auth::user()->cuenta_id;

        //busco plantilla por defecto
        $config = Doctrine::getTable('Config')->findOneByIdparAndNombre(1, 'default');

        //Busco Id de Plantilla a eliminar, almaceno valores a eliminar
        $cuentahasconfig = Doctrine::getTable('CuentaHasConfig')
            ->findOneByIdparAndConfigIdAndCuentaId(1, $plantilla_id, $cuenta_id);

        if (!$cuentahasconfig === FALSE && $config !== False) {
            $id_default = $config->id;
            $idpar_default = $config->idpar;

            $cuentahasconfig->idpar = $idpar_default;
            $cuentahasconfig->config_id = $id_default;
            $cuentahasconfig->cuenta_id = Auth::user()->cuenta_id;
            $cuentahasconfig->save();
        }

        $config = Doctrine::getTable('Config')->findOneByIdAndIdpar($plantilla_id, 1);
        $nombre_eliminar = $config->nombre;
        $config->delete();

        $source = 'uploads/themes/' . $cuenta_id . '/' . $nombre_eliminar . '/';
        $filedestino = 'application/views/themes/' . $cuenta_id . '/' . $nombre_eliminar . '/';
        File::deleteDirectory($source);
        File::deleteDirectory($filedestino);

        $cuenta_id = Auth::user()->cuenta_id;
        $data['config'] = Doctrine::getTable('Config')
            ->findByIdparAndCuentaIdOrCuentaId(1, $cuenta_id, 0);
        $cuentahasconfig = Doctrine::getTable('CuentaHasConfig')
            ->findOneByIdparAndCuentaId(1, Auth::user()->cuenta_id);
        $data['config_id'] = 1;

        if ($cuentahasconfig) {
            $data['config_id'] = $cuentahasconfig->config_id;
        }

        return redirect()->route('backend.configuration.template');
    }

    /**
     * @param string $conector_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Exception
     */
    public function modeler($conector_id = '')
    {
        if (!$conector_id == '') {
            $cuenta_id = Auth::user()->cuenta_id;
            $cuentahasconfig = Doctrine::getTable('CuentaHasConfig')->findOneByIdparAndCuentaId(2, $cuenta_id);
            if ($cuentahasconfig == FALSE) {
                $ctahascfg = new \CuentaHasConfig();
                $ctahascfg->idpar = 2;
                $ctahascfg->config_id = $conector_id;
                $ctahascfg->cuenta_id = $cuenta_id;
                $ctahascfg->save();

            } else {
                $cuentahasconfig->config_id = $conector_id;
                $cuentahasconfig->cuenta_id = Auth::user()->cuenta_id;
                $cuentahasconfig->save();
            }

            $data['config_id'] = $conector_id;
            $data['config'] = Doctrine::getTable('Config')->findByIdpar(2);
        } else {

            $data['config'] = Doctrine::getTable('Config')->findByIdpar(2);
            $cuentahasconfig = Doctrine::getTable('CuentaHasConfig')->findOneByIdparAndCuentaId(2, Auth::user()->cuenta_id);

            $data['config_id'] = 2;
            if ($cuentahasconfig) {
                $data['config_id'] = $cuentahasconfig->config_id;
            }

        }

        return view('backend.configuration.modeler.index', $data);
    }

    /* FIRMAS ELECTRONICAS*/
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function electronicSignature()
    {
        $firmas_electronicas = FirmaElectronica::where('cuenta_id',Auth::user()->cuenta_id)->get();
        return view('backend.configuration.electronic_signature.index', compact('firmas_electronicas') );
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addElectronicSignature()
    {
        $cuenta_id = Auth::user()->cuenta_id;
        $cuenta = Doctrine::getTable('Cuenta')->findOneById($cuenta_id);
        
        if($cuenta->entidad == NULL) {
            $mostrar = "Por favor Comuniquese con el Administrador";
        }
        else {
            //$mostrar = "<input name='entidad' id='name' type='text class='form-control' value='$cuenta->entidad' disabled>";
            $mostrar = $cuenta->entidad;
        }
        
        $data['entidad1'] = $mostrar;
        $data['form'] = new FirmaElectronica();
        $data['edit'] = false;
        $data['title'] = "Registro de Firma";
        $data['proposito'] = '';
        
        return view('backend.configuration.electronic_signature.edit', $data);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editElectronicSignature($id)
    {
        $cuenta_id = Auth::user()->cuenta_id;
        $cuenta = Doctrine::getTable('Cuenta')->findOneById($cuenta_id);
        //$array = $cuenta->toArray();
        if($cuenta->entidad == NULL) {
            $mostrar = "Por favor Comuniquese con el Administrador";
        }
        else {
            //$mostrar = "<input name='entidad' id='name' type='text class='form-control' value='$cuenta->entidad' disabled>";
            $mostrar = $cuenta->entidad;
        }
        
        $cuenta = Doctrine::getTable('HsmConfiguracion')->findOneById($id);

        $data['entidad1'] = $mostrar;
        $data['form'] = FirmaElectronica::find($id);
        $data['edit'] = true;
        $data['title'] = "Edición de Firma";
        $data['proposito'] = $cuenta->proposito;

        return view('backend.configuration.electronic_signature.edit', $data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeElectronicSignature(Request $request)
    {
        $this->saveElectronicSignature($request, new FirmaElectronica());

        $request->session()->flash('status', 'Firma registrada con éxito');

        return redirect()->route('backend.configuration.electronic_signature');
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateElectronicSignature(Request $request, $id)
    {
        $this->saveElectronicSignature($request, FirmaElectronica::find($id), true);

        $request->session()->flash('status', 'Datos de la Firma editados con éxito');

        return redirect()->route('backend.configuration.electronic_signature');
    }

    /**
     * @param Request $request
     * @param ElectronicSignature $user
     * @return ElectronicSignature
     */
    public function saveElectronicSignature(Request $request, FirmaElectronica $user, $edit = false)
    {
        $cuenta_id = Auth::user()->cuenta_id;
        $cuenta = Doctrine::getTable('Cuenta')->findOneById($cuenta_id);
        
        $this->validate($request, [
            'rut' => 'required|max:8',
            'nombre' => 'required|max:128',
            //'entidad' => 'required|max:256',
            'proposito' => 'required|max:64',
            'estado' => 'required|max:1'
        ]);
        
        $user->rut = $request->input('rut');
        $user->nombre = $request->input('nombre');
        $user->entidad = $cuenta->entidad;
        $user->proposito = $request->input('proposito');
        $user->estado = $request->input('estado');
        $user->cuenta_id = Auth::user()->cuenta_id;
        $user->save();

        return $user;
    }
    
    public function deleteElectronicSignature(Request $request, $id)
    {
        // Documento::where('hsm_configuracion_id',$id)->update(['hsm_configuracion_id' => NULL]);
        DB::table('documento')->where('hsm_configuracion_id',$id)->update(array('hsm_configuracion_id' => NULL));
        FirmaElectronica::destroy($id);

        $request->session()->flash('status', 'Firma eliminada con éxito');

        return redirect()->route('backend.configuration.electronic_signature');
    }
    
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function backendUsers(Request $request)
    {
        $email_search = $request->input('email_search', null);
        $order_by_email = $request->input('order_by_email', null);
        $order_by_name = $request->input('order_by_name', null);
        $order_by_lastname = $request->input('order_by_lastname', null);
        $order_by_rol = $request->input('order_by_rol', null);

        $users = UsuarioBackend::where('cuenta_id',Auth::user()->cuenta_id);

        if (null != $email_search)
            $users->where('email', 'like', "%$email_search%");

        if (null != $order_by_email) {
            $users->orderBy('email', $order_by_email);
            $order_by_email = ($order_by_email == 'asc') ? 'desc':'asc';
        }

        if (null != $order_by_name) {
            $users->orderBy('nombre', $order_by_name);
            $order_by_name = ($order_by_name == 'asc') ? 'desc':'asc';;
        }

        if (null != $order_by_lastname) {
            $users->orderBy('apellidos', $order_by_lastname);
            $order_by_lastname = ($order_by_lastname == 'asc') ? 'desc':'asc';
        }

        if (null != $order_by_rol) {
            $users->orderBy('rol', $order_by_rol);
            $order_by_rol = ($order_by_rol == 'asc') ? 'desc':'asc';
        }

        $users = $users->paginate(20);

        return view('backend.configuration.backend_users.index', [
            'users' => $users,
            'email_search' => $email_search ? $email_search:'',
            'order_by_email' => $order_by_email,
            'order_by_name' => $order_by_name,
            'order_by_lastname' => $order_by_lastname,
            'order_by_rol' => $order_by_rol
        ]);

    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addBackendUsers()
    {
        $data['form'] = new UsuarioBackend();
        $data['edit'] = false;

        return view('backend.configuration.backend_users.edit', $data);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editBackendUsers($id)
    {
        $data['form'] = UsuarioBackend::find($id);
        $data['edit'] = true;

        return view('backend.configuration.backend_users.edit', $data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeBackendUsers(Request $request)
    {
        $this->saveBackendUsers($request, new UsuarioBackend());

        $request->session()->flash('status', 'Usuario creado con éxito');

        return redirect()->route('backend.configuration.backend_users');
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateBackendUsers(Request $request, $id)
    {
        $this->saveBackendUsers($request, UsuarioBackend::find($id), true);

        $request->session()->flash('status', 'Usuario editado con éxito');

        return redirect()->route('backend.configuration.backend_users');
    }

    /* ESTILOS CONFIGURACION*/
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function myStyle()
    {
        $data = Auth::user()->Cuenta;
        $data['url_conf_defecto'] = url("/backend/configuracion/defecto");
        return view('backend.configuration.my_style.index', compact('data'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveMyStyle(Request $request)
    {
        $cuenta_id = Auth::user()->cuenta_id; //1
        $data = Cuenta::find(Auth::user()->cuenta_id); //datos de la cuenta identificada con el id
        $this->validate($request, [
            'boton_iniciar_sesion' => 'required',
            'texto_iniciar_sesion' => 'required',   
            'boton_iniciar_sesion_on_mouse' => 'required',
            'texto_iniciar_sesion_on_mouse' => 'required',
            'tarjeta_header' => 'required',         
            'texto_tarjeta_header' => 'required',   
            'tarjeta_footer' => 'required',         
            'texto_tarjeta_footer' => 'required',   
            'tramite_boton1' => 'required',
            'texto_tramite_boton1' => 'required',
            'tramite_boton2' => 'required',         //boton siguiente
            'texto_tramite_boton2' => 'required',
            'tramite_boton2_on_mouse' => 'required',         //boton siguiente
            'texto_tramite_boton2_on_mouse' => 'required',
            'tramite_boton3' => 'required',         //boton volver
            'texto_tramite_boton3' => 'required',
            'tramite_boton3_on_mouse' => 'required',         
            'texto_tramite_boton3_on_mouse' => 'required',
            'tramite_linea' => 'required',
            'activo' => 'required'
        ]);
        
        $estilos = "a.nav-link:hover{border-radius:4px;color:".$request->input('texto_iniciar_sesion_on_mouse').";background-color: ".$request->input('boton_iniciar_sesion_on_mouse')."}.nav-item.login.btn-white a.nav-link {color: ".$request->input('texto_iniciar_sesion').";-webkit-transition: all .2s ease;transition: all .2s ease;background-color: ".$request->input('boton_iniciar_sesion').";}.card .card-header.draft {color: ".$request->input('texto_tarjeta_header').";background: ".$request->input('tarjeta_header').";}.card a.card-footer {border: none;color: ".$request->input('texto_tarjeta_footer')."font-size: 16px;text-align: left;background-color: ".$request->input('tarjeta_footer').";}.simple-list-menu a.list-group-item.active, .simple-list-menu a.list-group-item:hover {background-color: ".$request->input('tramite_boton1').";color: ".$request->input('texto_tramite_boton1').";}.btn-danger {color: ".$request->input('texto_tramite_boton2').";background-color: ".$request->input('tramite_boton2').";border-color: ".$request->input('tramite_boton2').";}.btn-danger:hover{color: ".$request->input('texto_tramite_boton2_on_mouse').";background-color: ".$request->input('tramite_boton2_on_mouse').";border-color: ".$request->input('tramite_boton2_on_mouse').";}.btn-light {color: ".$request->input('texto_tramite_boton3').";background-color: ".$request->input('tramite_boton3').";border-color: ".$request->input('tramite_boton3').";}.btn-light:hover{color: ".$request->input('texto_tramite_boton3_on_mouse').";background-color: ".$request->input('tramite_boton3_on_mouse').";border-color: ".$request->input('tramite_boton3_on_mouse').";}ul.steps li.active {border-bottom: 8px solid ".$request->input('tramite_linea').";}";
        $estilos = json_encode(addslashes($estilos));

        $cuenta = Doctrine::getTable('cuenta')->findOneById($cuenta_id);
        $cuenta->personalizacion = $estilos;
        $cuenta->personalizacion_estado = $request->activo;
        $cuenta->save();        
        
        $request->session()->flash('status', 'Estilos guardados con éxito');
        
        return redirect()->route('backend.configuration.my_style');
    }
    
    /**
     * @param Request $request
     * @param UsuarioBackend $user
     * @return UsuarioBackend
     */
    public function saveBackendUsers(Request $request, UsuarioBackend $user, $edit = false)
    {
        $validations = [
            'nombre' => 'required|max:128',
            'apellidos' => 'required|max:128',
            'rol' => 'required'
        ];

        if (!$edit) {
            $validations['email'] = 'required|email|min:6|max:128|unique:usuario_backend';
            $user->email = $request->input('email');
        }

        $password = $request->input('password', null);

        if ($password) {
            $validations['password'] = [
                'required',
                'min:6',
                'confirmed',
                new SafetyPassword
            ];
            $user->password = Hash::make($password);
        }

        $request->validate($validations);

        $user->nombre = $request->input('nombre');
        $user->apellidos = $request->input('apellidos');
        $user->rol = implode(",", $request->input('rol'));
        $user->cuenta_id = Auth::user()->cuenta_id;

        $user->save();

        return $user;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function frontendUsers(Request $request)
    {
        $email_search = $request->input('email_search', null);
        $order_by_email = $request->input('order_by_email', null);
        $order_by_name = $request->input('order_by_name', null);
        $order_by_lastname = $request->input('order_by_lastname', null);
        $order_by_usuario = $request->input('order_by_usuario', null);


        $users = User::whereCuentaId(Auth::user()->cuenta_id);

        if (null != $email_search)
            $users->where('email', 'like', "%$email_search%");

        if (null != $order_by_usuario) {
            $users->orderBy('usuario', $order_by_usuario);
            $order_by_usuario = ($order_by_usuario == 'asc') ? 'desc':'asc';
        }

        if (null != $order_by_email) {
            $users->orderBy('email', $order_by_email);
            $order_by_email = ($order_by_email == 'asc') ? 'desc':'asc';
        }

        if (null != $order_by_name) {
            $users->orderBy('nombres', $order_by_name);
            $order_by_name = ($order_by_name == 'asc') ? 'desc':'asc';
        }

        if (null != $order_by_lastname) {
            $users->orderBy('apellido_paterno', $order_by_lastname);
            $order_by_lastname = ($order_by_lastname == 'asc') ? 'desc':'asc';
        }

        $users = $users->paginate(20);

        return view('backend.configuration.frontend_users.index', [
            'users' => $users,
            'email_search' => $email_search,
            'order_by_email' => $order_by_email,
            'order_by_name' => $order_by_name,
            'order_by_lastname' => $order_by_lastname,
            'order_by_usuario' => $order_by_usuario
        ]);

    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addFrontendUsers()
    {
        $data['form'] = new User();
        $data['grupos'] = GrupoUsuarios::where('id', Auth::user()->cuenta_id)->get();
        $data['edit'] = false;

        return view('backend.configuration.frontend_users.edit', $data);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editFrontendUsers($id)
    {
        $data['form'] = User::find($id);
        $data['grupos'] = GrupoUsuarios::whereCuentaId(Auth::user()->cuenta_id)->get();
        $data['grupos_selected'] = $data['form']->grupo_usuarios()->get()->groupBy('id')->keys()->toArray();
        $data['edit'] = true;

        return view('backend.configuration.frontend_users.edit', $data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeFrontendUsers(Request $request)
    {
        $this->saveFrontendUsers($request, new User());

        $request->session()->flash('status', 'Usuario creado con éxito');

        return redirect()->route('backend.configuration.frontend_users');
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateFrontendUsers(Request $request, $id)
    {
        $this->saveFrontendUsers($request, User::find($id), true);

        $request->session()->flash('status', 'Usuario editado con éxito');

        return redirect()->route('backend.configuration.frontend_users');
    }

    /**
     * @param Request $request
     * @param User $user
     * @param bool $edit
     * @return User
     */
    public function saveFrontendUsers(Request $request, User $user, $edit = false)
    {
        $validations = [
            'nombres' => 'required',
            'email' => 'required|email|min:6|max:128',
        ];

        if (!$edit) {
            $user->usuario = $request->input('usuario');
            $validations['usuario'] = 'required|unique:usuario';
        } else {
            // al editar sólo valido email unico si es diferente al valor que ya tengo registrado
            if ($user->email != $request->input('email')) {
                $validations['email'] = 'required|email|min:6|max:128|unique:usuario';
            }
        }

        $password = $request->input('password', null);

        if ($password) {
            $validations['password'] = [
                'required',
                'min:6',
                'confirmed',
                new SafetyPassword
            ];

            $user->password = Hash::make($password);
        }



        $this->validate($request, $validations, []);

        $user->nombres = $request->input('nombres');
        $user->apellido_paterno = $request->input('apellido_paterno');
        $user->apellido_materno = $request->input('apellido_materno');
        $user->vacaciones = $request->has('vacaciones') ? 1 : 0;
        $user->email = $request->input('email');
        $user->cuenta_id = Auth::user()->cuenta_id;
        $user->salt = '';
        $user->save();

        //Eliminamos todas las relaciones que tenga este usuario con grupos
        $user->grupo_usuarios()->detach();

        //Insertamos las nuevas relaciones
        if ($request->has('grupos_usuarios')) {
            foreach ($request->input('grupos_usuarios') as $id) {
                $user->grupo_usuarios()->attach($id);
            }
        }

        return $user;
    }


    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteFrontendUsers(Request $request, $id)
    {
        $error_msg = 'Lo sentimos no hemos podido ejecutar la acción solicitada.';

        try {
            User::destroy($id);
            $request->session()->flash('status', 'Usuario eliminado con éxito');
        } catch (QueryException $e){

            if (!isset($e->errorInfo[1])) {
                $request->session()->flash('warning', $error_msg);
                return redirect()->route('backend.configuration.frontend_users');
            }

            switch ($e->errorInfo[1]) {
                case 1451:
                    $error_msg = 'El usuario no puede ser eliminado, asegúrate de que éste no cuente con trámites pendientes.';
                    break;
            }

            $request->session()->flash('warning', $error_msg);


        } catch (\Exception $e) {
            $request->session()->flash('warning', $error_msg);
        }

        return redirect()->route('backend.configuration.frontend_users');
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteBackendUsers(Request $request, $id)
    {
        UsuarioBackend::destroy($id);

        $request->session()->flash('status', 'Usuario eliminado con éxito');

        return redirect()->route('backend.configuration.backend_users');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function groupUsers()
    {
        $group_users = GrupoUsuarios::whereCuentaId(Auth::user()->cuenta_id)->orderBy('id', 'asc')->get();

        return view('backend.configuration.group_users.index', compact('group_users'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addGroupUsers()
    {
        $data['form'] = new GrupoUsuarios();
        $data['usuarios'] = User::whereCuentaId(Auth::user()->cuenta_id)->whereNotNull('email')->get();
        $data['edit'] = false;

        return view('backend.configuration.group_users.edit', $data);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editGroupUsers($id)
    {
        $data['form'] = GrupoUsuarios::find($id);
        $data['usuarios'] = User::whereCuentaId(Auth::user()->cuenta_id)->whereNotNull('email')->get();
        $data['usuarios_selected'] = $data['form']->users()->get()->groupBy('id')->keys()->toArray();
        $data['edit'] = true;

        return view('backend.configuration.group_users.edit', $data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeGroupUsers(Request $request)
    {
        $this->saveGroupUsers($request, new GrupoUsuarios());

        $request->session()->flash('status', 'Grupo de Usuarios creado con éxito');

        return redirect()->route('backend.configuration.group_users');
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateGroupUsers(Request $request, $id)
    {
        $this->saveGroupUsers($request, GrupoUsuarios::find($id), true);

        $request->session()->flash('status', 'Grupo de Usuarios editado con éxito');

        return redirect()->route('backend.configuration.group_users');
    }

    /**
     * @param Request $request
     * @param GrupoUsuarios $grupo_usuarios
     * @param bool $edit
     * @return GrupoUsuarios
     */
    public function saveGroupUsers(Request $request, GrupoUsuarios $grupo_usuarios, $edit = false)
    {
        $this->validate($request, [
            'nombre' => 'required'
        ]);

        $grupo_usuarios->nombre = $request->input('nombre');
        $grupo_usuarios->cuenta_id = Auth::user()->cuenta_id;
        $grupo_usuarios->save();

        //Eliminamos todas las relaciones con usuarios que tenga este grupo
        $grupo_usuarios->users()->detach();

        //Insertamos las nuevas relaciones
        if ($request->has('usuarios')) {
            foreach ($request->input('usuarios') as $id_user) {
                $grupo_usuarios->users()->attach($id_user);
            }
        }


        return $grupo_usuarios;
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteGroupUsers(Request $request, $id)
    {
        GrupoUsuarios::destroy($id);

        $request->session()->flash('status', 'Grupo Usuario eliminado con éxito');

        return redirect()->route('backend.configuration.group_users');
    }

    /**
     * @param Request $request
     * @return array
     */
    public function mySiteUploadLogo(Request $request)
    {
        $allowedExtensions = ['jpg', 'png'];
        $pathLogos = public_path('logos/');
        $response = (new FileUploader($allowedExtensions))->handleUpload($pathLogos);

        return $response;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function mySiteUploadLogof(Request $request)
    {
        $allowedExtensions = ['jpg', 'png'];
        $pathLogos = public_path('logos/');
        $response = (new FileUploader($allowedExtensions))->handleUpload($pathLogos);

        return $response;
    }
    
    /**
     * @param Request $request
     * @return array
     */
    public function mySiteUploadTheme(Request $request)
    {
        $cuenta = Auth::user()->cuenta_id;
        $ruta_uploads = public_path('themes/' . $cuenta . '/');
        $ruta_views = resource_path('views/themes/' . $cuenta . '/');

        is_dir(public_path('themes/')) ? TRUE : mkdir(public_path('themes/'));
        is_dir(resource_path('views/themes/')) ? TRUE : mkdir(resource_path('views/themes/'));
        is_dir($ruta_uploads) ? TRUE : mkdir($ruta_uploads);
        is_dir($ruta_views) ? TRUE : mkdir($ruta_views);

        $allowedExtensions = ['zip'];
        $sizeLimit = 20 * 1024 * 1024;

        $result = (new FileUploader($allowedExtensions, $sizeLimit))->handleUpload($ruta_uploads, true);

        if (isset($result['success'])) {
            $archivo = $result['full_path'];
            $partes_ruta = pathinfo($archivo);
            $directorio = $partes_ruta['dirname'];
            $filename = $partes_ruta['filename'];

            if ($filename == 'default') {
                $filename = 'default' . $cuenta;
            }

            $source = $ruta_uploads . $filename . '/';
            $zip = new \ZipArchive;

            if ($zip->open($archivo) === TRUE) {
                $zip->extractTo($source);
                $zip->close();
                unlink($archivo);
            }

            $fileorigen = $source . 'template.php';
            $filedestino = $ruta_views . $filename . '/template.php';
            if (file_exists($fileorigen)) {
                if (file_exists($filedestino)) {
                    unlink($filedestino);
                } else if (!is_dir(dirname($filedestino))) {
                    mkdir(dirname($filedestino));
                }

                rename($fileorigen, $filedestino);
            }

            $result['full_path'] = $source . 'preview.png';
            $result['file_name'] = 'preview.png';
            $result['folder'] = $filename;
        }

        return $result;
    }

    /**
     * @param Request $request
     */
    public function ajax_get_validacion_reglas(Request $request)
    {

        $rule = (isset($_GET['rule'])) ? $_GET['rule'] : '';
        $proceso_id = (isset($_GET['proceso_id'])) ? $_GET['proceso_id'] : '';

        Log::debug('ajax_get_validacion_reglas() $rule [' . $rule . ']');

        $code = 200;

        if (strlen($rule) > 0) {
            $regla = new \Regla($rule);
            $mensaje = $regla->validacionVariablesEnReglas($proceso_id);

            if (isset($mensaje) && count($mensaje) == 0) {
                $code = 202;
            } else {
                $mensaje = "Las sgtes. variables no existen: <br>" . implode(', ', $mensaje);
            }
        } else {
            $code = 202;
            $mensaje = "";
        }

        return response()->json(array('code' => $code, 'mensaje' => $mensaje));
    }

    public function masiva(Request $request)
    {
        $allowedExtensions = ['csv'];
        $pathLogos = public_path('uploads/tmp/');
        is_dir(public_path('uploads/tmp/')) ? TRUE : mkdir(public_path('uploads/tmp/'));
        $puede_escribir = is_writable($pathLogos) ? 1 : 0;
        $response = (new FileUploader($allowedExtensions))->handleUpload($pathLogos);
        return $response;
    }

    public function list_categoria()
    {
        $data['categorias'] = Doctrine::getTable('Categoria')->findAll();
        $data['title'] = 'Categorias';
        $data['content'] = view('backend.configuration.list_categoria.index', $data);
        
          return view('layouts.backend', $data);      
       
    }

    public function edit_category($id = null)
    {
        if ($id) {
            $categoria = Doctrine::getTable('Categoria')->find($id);
        } else {
            $categoria = new Categoria();
        }

        $data['categoria'] = $categoria;
        $data['title'] = $categoria->id ? 'Editar' : 'Crear';
        $data['content'] = view('backend.configuration.list_categoria.edit_category', $data);

        return view('layouts.backend', $data);
    }


    public function editar_lista_categoria(Request $request, $id = null)
    {

        try {
            if ($id)
                $categoria = Doctrine::getTable('Categoria')->find($id);
            else
                $categoria = new Categoria();

            $request->validate([
                'nombre' => 'required',
                'descripcion' => 'required',
                'logo' => 'required',
            ]);

            $respuesta = new \stdClass();

            // Cuenta
            $categoria->nombre = $request->input('nombre');
            $categoria->descripcion = $request->input('descripcion');
          
            if ($request->input('logo') != "nologo.png") {
                $categoria->icon_ref = $request->input('logo');
            }

            $categoria->save();

            $id = (int)$categoria->id;

            if ($id > 0) {
                $request->session()->flash('success', 'Categoria guardada con éxito.');
                $respuesta->validacion = true;
                $respuesta->redirect = url('/backend/configuracion/categorias/');

            } else {
                $respuesta->validacion = false;
                $respuesta->errores = '<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>Ocurrió un error al guardar los datos.</div>';
            }

        } catch (Exception $ex) {
            $respuesta->validacion = false;
            $respuesta->errores = '<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>' . $ex->getMessage() . '</div>';
        }

        return response()->json($respuesta);

    }

    public function eliminar_categoria(Request $request, $id)
    {
        $categoria = Doctrine::getTable('Categoria')->find($id);
        $categoria->delete();

        $request->session()->flash('success', 'Categoria eliminada con éxito.');
        return redirect('/backend/configuracion/categorias/');
    }

    public function conf_defecto()
    {
        $data = Auth::user()->Cuenta;
        $data->personalizacion = NULL;
        $data->personalizacion_estado = '0';
        $data->save();
        return redirect('/backend/configuracion/estilo/');
    }

   
}

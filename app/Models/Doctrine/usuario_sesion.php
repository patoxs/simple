<?php

use Illuminate\Support\Facades\Auth;
use App\Helpers\Doctrine;

class UsuarioSesion
{

    private static $user;

    private function __construct()
    {

    }

    public static function usuario()
    {

        if (!isset(self::$user)) {


            if (!Auth::check()) {
                return FALSE;
            }

            if (!$u = Doctrine::getTable('Usuario')->find(Auth::user()->id)) {
                return FALSE;
            }

            self::$user = $u;
        }

        return self::$user;
    }

    public static function force_login()
    {
        $CI = &get_instance();
        if ($CI->session->flashdata('openidcallback')) {
            self::login_open_id();
        }
        /*
        $CI->load->library('LightOpenID');
        if ($CI->lightopenid->mode == 'id_res') {
            self::login_open_id();
        }
        */
        if (!self::usuario()) {
            //Creo un usuario no registrado
            $usuario = new Usuario();
            $usuario->usuario = random_string('unique');
            $usuario->setPasswordWithSalt(random_string('alnum', 32));
            $usuario->registrado = 0;
            $usuario->save();

            $CI->session->set_userdata('usuario_id', $usuario->id);
            self::$user = $usuario;
        }
    }

    public static function login($usuario, $password)
    {
        $CI = &get_instance();

        $u = self::validar_acceso($usuario, $password);

        if ($u) {

            //Logueamos al usuario
            $CI->session->set_userdata('usuario_id', $u->id);
            self::$user = $u;

            return TRUE;
        }

        return FALSE;
    }

    /**
     * Esta operación solo es usada por la API REST
     * @param type $usuario_o_email
     * @param type $password
     * @return boolean
     */
    public static function registrarUsuario($usuario)
    {
        log_message('INFO', "UsuarioSesion - Registrando usuario " . $usuario);
        if ($usuario == NULL) {
            return NULL;
        }

        $CI = &get_instance();
        //No se valida el usuario por que se supone validado por la API
        $users = Doctrine::getTable('Usuario')->findByUsuarioAndOpenId($usuario, 0);

        if ($users->count() == 0) {
            $users = Doctrine::getTable('Usuario')->findByEmailAndOpenId($usuario, 0);
        }
        if ($users->count() == 0) {
            return FALSE;
        }
        //Usuarios validados en cuanto a existencia de la cuenta

        $u_input = FALSE;
        foreach ($users as $u) {
            if ($u->usuario == $usuario || $u->email == $usuario) {
                $u_input = $u;
                break;
            }
        }

        if ($u_input) {
            //Logueamos al usuario
            $CI->session->set_userdata('usuario_id', $u_input->id);
            self::$user = $u_input;

            return TRUE;
        }

        return FALSE;
    }


    public static function validar_acceso($usuario_o_email, $password)
    {
        $users = Doctrine::getTable('Usuario')->findByUsuarioAndOpenId($usuario_o_email, 0);

        if ($users->count() == 0) {
            $users = Doctrine::getTable('Usuario')->findByEmailAndOpenId($usuario_o_email, 0);
        }

        if ($users->count() == 0) {
            return FALSE;
        }


        foreach ($users as $u) {    //Se debe chequear en varias cuentas, ya que en las cuentas del legado (antiguas) podian haber usuarios con el mismo correo.
            // this mutates (encrypts) the input password
            $u_input = new Usuario();
            $u_input->setPasswordWithSalt($password, $u->salt);

            // password match (comparing encrypted passwords)
            if ($u->password == $u_input->password) {
                unset($u_input);


                return $u;
            }

            unset($u_input);
        }

        return FALSE;
    }

    private static function login_open_id()
    {


        $CI = &get_instance();
        /*
        if ($CI->lightopenid->validate() && strpos($CI->lightopenid->claimed_id, 'https://www.claveunica.cl/') === 0) {
            $atributos = $CI->lightopenid->getAttributes();
            $usuario = Doctrine::getTable('Usuario')->findOneByUsuarioAndOpenId($CI->lightopenid->claimed_id, 1);
            if (!$usuario) {
                $usuario = new Usuario();
                $usuario->usuario = $CI->lightopenid->claimed_id;
                $usuario->registrado = 1;
                $usuario->open_id = 1;
            }
            $usuario->rut = $atributos['person/guid'];
            $usuario->save();

            $CI->session->set_userdata('usuario_id', $usuario->id);
            self::$user = $usuario;
        }
        */
        $rut = $CI->session->flashdata('rut') ? $CI->session->flashdata('rut') : $CI->session->userdata('usuario_rut');
        $nombres = $CI->session->flashdata('nombres') ? $CI->session->flashdata('nombres') : "";
        $apellidoPaterno = $CI->session->flashdata('apellidoPaterno') ? $CI->session->flashdata('apellidoPaterno') : "";
        $apellidoMaterno = $CI->session->flashdata('apellidoMaterno') ? $CI->session->flashdata('apellidoMaterno') : "";

        $usuario = Doctrine::getTable('Usuario')->findOneByRutAndOpenId($rut, 1);

        if (!$usuario) {
            $usuario = new Usuario();
            $usuario->usuario = $rut;
            $usuario->nombres = $nombres;
            $usuario->apellido_paterno = $apellidoPaterno;
            $usuario->apellido_materno = $apellidoMaterno;
            $usuario->registrado = 1;
            $usuario->open_id = 1;
        }
        $usuario->rut = $rut;

        $usuario->save();
        $CI->session->set_userdata('usuario_id', $usuario->id);
        $CI->session->set_userdata('usuario_rut', $usuario->rut);
        $CI->session->set_userdata('usuario_login', 1);
        self::$user = $usuario;
    }

    public static function logout()
    {
        setcookie('redirectlogin', '', time() - 3600);
        $CI = &get_instance();
        self::$user = NULL;
        $CI->session->unset_userdata('usuario_id');
    }

    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    /**
     * Crea un usuario anonimo para la capa de servicios
     */
    public static function createAnonymousSession()
    {
        $anonimo = new Usuario();
        $anonimo->usuario = random_string('unique');
        $anonimo->setPasswordWithSalt(random_string('alnum', 32));
        $anonimo->save();
        $CI = &get_instance();
        $CI->session->set_userdata('usuario_id', $anonimo->id);
        log_message('info', 'Usaurio no tiene registada un sesion');
    }

}
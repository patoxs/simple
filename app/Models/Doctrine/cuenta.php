<?php

use App\Helpers\Doctrine;
use Illuminate\Support\Facades\Log;

class Cuenta extends Doctrine_Record
{

function setTableDefinition()
    {
        $this->hasColumn('id');
        $this->hasColumn('nombre');
        $this->hasColumn('nombre_largo');
        if(\Schema::hasColumn('cuenta', 'analytics')){
        $this->hasColumn('analytics'); //analytics
        }
        $this->hasColumn('mensaje');
        $this->hasColumn('logo');
        if(\Schema::hasColumn('cuenta', 'logof')){
            $this->hasColumn('logof');
        }

        $this->hasColumn('api_token');
        $this->hasColumn('descarga_masiva');
        $this->hasColumn('client_id');
        $this->hasColumn('client_secret');
        $this->hasColumn('ambiente');
        $this->hasColumn('vinculo_produccion');
        if(\Schema::hasColumn('cuenta', 'entidad')){
            $this->hasColumn('entidad');
        }
        if(\Schema::hasColumn('cuenta', 'estilo')){
            $this->hasColumn('estilo');
        }
        if(\Schema::hasColumn('cuenta', 'header')){
            $this->hasColumn('header');
        }

        if(\Schema::hasColumn('cuenta', 'footer')){
            $this->hasColumn('footer');
        }

        if(\Schema::hasColumn('cuenta', 'personalizacion')){
            $this->hasColumn('personalizacion');
        }

        if(\Schema::hasColumn('cuenta', 'personalizacion_estado')){
            $this->hasColumn('personalizacion_estado');
        }

        if(\Schema::hasColumn('cuenta', 'seo_tags')){
            $this->hasColumn('seo_tags');
        }

        if(\Schema::hasColumn('cuenta', 'metadata')){
            $this->hasColumn('metadata');
        }

        if(\Schema::hasColumn('cuenta', 'favicon')){
            $this->hasColumn('favicon');
        }
    }

    function setUp()
    {
        parent::setUp();

        $this->hasMany('UsuarioBackend as UsuariosBackend', array(
            'local' => 'id',
            'foreign' => 'cuenta_id'
        ));

        $this->hasMany('Usuario as Usuarios', array(
            'local' => 'id',
            'foreign' => 'cuenta_id'
        ));

        $this->hasMany('GrupoUsuarios as GruposUsuarios', array(
            'local' => 'id',
            'foreign' => 'cuenta_id'
        ));

        $this->hasMany('Proceso as Procesos', array(
            'local' => 'id',
            'foreign' => 'cuenta_id'
        ));

        $this->hasMany('Widget as Widgets', array(
            'local' => 'id',
            'foreign' => 'cuenta_id',
            'orderBy' => 'posicion'
        ));

        $this->hasMany('HsmConfiguracion as HsmConfiguraciones', array(
            'local' => 'id',
            'foreign' => 'cuenta_id'
        ));
    }

    public function updatePosicionesWidgetsFromJSON($json)
    {
        $posiciones = json_decode($json);

        Doctrine_Manager::connection()->beginTransaction();
        foreach ($this->Widgets as $c) {
            $c->posicion = array_search($c->id, $posiciones);
            $c->save();
        }
        Doctrine_Manager::connection()->commit();
    }

    // Retorna el objecto cuenta perteneciente a este dominio.
    // Retorna null si no estamos en ninguna cuenta valida.
    public static function cuentaSegunDominio()
    {
        static $firstTime = true;
        static $cuentaSegunDominio = null;
        if ($firstTime) {
            $firstTime = false;
            $host = Request::server('HTTP_HOST');
            Log::debug('$host: ' . $host);
            $main_domain = env('APP_MAIN_DOMAIN');

            if ($main_domain) {
                Log::debug('$main_domain2: ' . $main_domain);
                $main_domain = addcslashes($main_domain, '.');
                preg_match('/(.+)\.' . $main_domain . '/', $host, $matches);
                Log::debug('$main_domain2: ' . $main_domain);

                if (isset ($matches[1])) {
                    Log::debug('$matches: ' . $matches[1]);
                    $cuentaSegunDominio = Doctrine::getTable('Cuenta')->findOneByNombre($matches[1]);
                }
            }

            if(!$cuentaSegunDominio){
                $cuentaSegunDominio = Doctrine_Query::create()->from('Cuenta c')->limit(1)->fetchOne();
            }
        }

        return $cuentaSegunDominio;
    }

    public static function getAccountFavicon()
    {
        $cuenta = self::cuentaSegunDominio();
        $favicon = '/img/favicon.png';
        if ($cuenta['favicon'] != null) {
            $favicon = '/logos/'.$cuenta['favicon'];
        }
        return $favicon;
    }

    public static function getAccountMetadata()
    {
        $cuenta = self::cuentaSegunDominio();
        $metadata = null;
        if ($cuenta['metadata'] != null) {
            $metadata = json_decode($cuenta['metadata']);
        }
        return $metadata;
    }


    public function getLogoADesplegar()
    {
        if ($this->logo)
            return asset('logos/' . $this->logo);
        else
            return asset('img/logo.png');
    }

    public function getLogofADesplegar()
    {
        if ($this->logof)
            return asset('logos/' . $this->logof);
        else
            return asset('img/logof.png');
    }

    public function usesClaveUnicaOnly()
    {
        foreach ($this->Procesos as $p) {
            $tareaInicial = $p->getTareaInicial();
            if ($tareaInicial && $tareaInicial->acceso_modo != 'claveunica')
                return false;
        }

        return true;
    }

    public function getAmbienteDev($cuenta_prod_id)
    {

        $cuenta_dev = Doctrine_Query::create()
            ->from('Cuenta c')
            ->where('c.vinculo_produccion = ?', $cuenta_prod_id)
            ->execute();

        return $cuenta_dev;
    }

    public function getProcesosActivos()
    {

        Log::debug('getProcesosActivos: ' . $this->id);

        $procesos = Doctrine_Query::create()
            ->from('Proceso p, p.Cuenta c')
            ->where('p.activo=1 AND c.id = ?', $this->id)
            ->execute();

        return $procesos;
    }

    // Retorna el valor de header, footer, css  perteneciente a este dominio.
    // Retorna null en personalizacion si no esta activado (1) .
    public static function configSegunDominio()
    {
        static $configSegunDominio = null;
        if(is_null($configSegunDominio ) ){
            $configSegunDominio = self::cuentaSegunDominio();
            $configDominio['estilo'] = $configSegunDominio->estilo;
            $configDominio['dominio_header'] = $configSegunDominio->header;
            $configDominio['analytics'] = $configSegunDominio->analytics; //añado analytics
            $configDominio['dominio_footer'] = $configSegunDominio->footer;
            $configDominio['metadata_footer'] = json_decode($configSegunDominio->metadata);
            $configDominio['personalizacion'] = "1" == $configSegunDominio->personalizacion_estado ? $configSegunDominio->personalizacion : '';
            $configDominio['personalizacion_estado'] = $configSegunDominio->personalizacion_estado;
        }
        return $configDominio;
    }

    public static function seo_tags($cuenta_id = null){
        static $seo_tags = null;
        if(is_null($seo_tags)){
            if(is_null($cuenta_id)){
                $seo = isset(self::cuentaSegunDominio()->seo_tags) ? self::cuentaSegunDominio()->seo_tags: null;
            }else{
                $cuenta = Doctrine::getTable('Cuenta')->findOneById($cuenta_id);
                
                $seo = $cuenta ? $cuenta->seo_tags : null;
            }
            
            $seo_tags = json_decode($seo);
            // corregimos cualquier posible caso invalido
            $seo_tags = ( ! is_null($seo_tags ) && is_object($seo_tags)) ? $seo_tags : new StdClass();
            $default_tags = [
                'title' => config('app.name', 'Laravel'),
                'description' => 'Simple',
                'keywords' => 'Simple',
               // 'analytics' => Doctrine::getTable('Cuenta')->findOneById(1)->analytics
             ];
            foreach ($default_tags as $key => $value) {
                if(! isset($seo_tags->$key)){
                    $seo_tags->$key = $value;
                }
            }
            $seo_tags->title = ucwords($seo_tags->title);
            if( is_array($seo_tags->keywords) ){
                $seo_tags->keywords = implode(',', $seo_tags->keywords);
            }
        }
           
        return $seo_tags;
    }
}
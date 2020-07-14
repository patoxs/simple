<?php

class Connect_services
{
    private $appkey = '';
    private $domain = '';
    private $cuenta = 0;
    private $componente = '';
    private $base_services = '';
    private $context = '';
    private $num_rows = 0;

    function __construct()
    {
        $this->componente = 'token_services';
        $this->cuenta = 1;
        $this->num_rows = env('RECORDS');
        $this->appkey = env('AGENDA_APP_KEY');
    }

    public function getAppkey()
    {
        return $this->appkey;
    }

    public function setAppkey($appkey = '')
    {
        $trimAppkey = trim($appkey);
        if (isset($trimAppkey) && !empty($trimAppkey)) {
            $this->appkey = $trimAppkey;
        } else {
            throw new Exception('El Appkey no puede estar vacio');
        }
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function setDomain($domain = '')
    {
        $trimDomain = trim($domain);
        if (isset($trimDomain) && !empty($trimDomain)) {
            $this->domain = $domain;
        } else {
            throw new Exception('El Dominio no puede estar vacio');
        }
    }

    public function getCuenta()
    {
        return $this->cuenta;
    }

    public function setCuenta($cuenta = 0)
    {
        if (isset($cuenta) && is_numeric($cuenta)) {
            $this->cuenta = $cuenta;
        } else {
            throw new Exception('La cuenta de simple no puede estar vacia y debe ser numerica');
        }
    }

    public function getComponente()
    {
        $this->componente;
    }

    public function setComponente($componente = '')
    {
        $trimComponente = trim($componente);
        if (isset($trimComponente) && !empty($trimComponente)) {
            $this->componente = $componente;
        } else {
            throw new Exception('El componente no puede estar vacio');
        }
    }

    public function getBaseService()
    {
        return $this->base_services;
    }

    public function setBaseService($uri = '')
    {
        $trimUri = trim($uri);
        if (isset($trimUri) && !empty($trimUri)) {
            if (preg_match("/^h(t){2}p(s)?:(\/){2}([a-zA-Z0-9])+([a-zA-Z0-9\._-])*(:([0-9])*)?(\/)?$/", $uri)) {
                $this->base_services = $uri;
            } else {
                throw new Exception('La url tiene un formato incorrecto');
            }
        } else {
            throw new Exception('La url del servicio no puede estar vacia');
        }
    }

    public function getContext()
    {
        return $this->context;
    }

    public function setContext($context = '')
    {
        $trimContext = trim($context);
        if (isset($trimContext) && !empty($trimContext)) {
            if (preg_match("/^(\/)?([a-zA-Z0-9])+([a-zA-Z0-9\._-])*(\/)?$/", $context)) {
                $this->context = $context;
            } else {
                throw new Exception('El contexto un formato incorrecto');
            }
        }
    }

    public function getNumeroRegistroPagina()
    {
        return $this->num_rows;
    }

    public function setNumeroRegistroPagina($num = 0)
    {
        if (isset($num) && is_numeric($num) && $num > 0) {
            $this->num_rows = $num;
        } else {
            throw new Exception('El numero de registros por pagina debe ser mayor a 0');
        }
    }

    public function save()
    {
        try {
            $this->validateAll();

            $objdomain = new Config_general();

            $objdomain->componente = $this->componente;
            $objdomain->cuenta = $this->cuenta;
            $objdomain->llave = 'domain';
            $objdomain->valor = $this->domain;

            if ($this->isCreate()) {
                $objdomain->actualizar();
            } else {
                $objdomain->save();

            }
        } catch (Exception $err) {
            throw new Exception($err->getMessage());
        }
    }


    private function validateAll()
    {
        $trimAppkey = trim($this->appkey);
        $trimDomain = trim($this->domain);
        $trimComponente = trim($this->componente);
        if (empty($trimAppkey)) {
            throw new Exception('El Appkey no puede estar vacio');
        }
        if (empty($trimDomain)) {
            throw new Exception('El Dominio no puede estar vacio');
        }
        if (empty($trimComponente)) {
            throw new Exception('El componente no puede estar vacio');
        }
        if (!is_numeric($this->cuenta) || $this->cuenta == 0) {
            throw new Exception('La cuenta de simple no puede estar vacia y debe ser numerica');
        }

    }

    private function isCreate()
    {
        try {
            $result = Doctrine_Query::create()
                ->select('COUNT(componente) AS cuenta')
                ->from('config_general')
                ->where("componente = ? AND cuenta = ?", array('token_services', $this->cuenta))
                ->execute();
            if ($result[0]->cuenta >= 1) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $err) {
            throw new Exception($err->getMessage());
        }
    }

    private function loadCampo($value_llave)
    {
        try {
            $result = Doctrine_Query::create()
                ->select('valor')
                ->from('config_general')
                ->where("componente=? AND cuenta=? and llave=?", array('token_services', $this->cuenta, $value_llave))
                ->execute();
            if (isset($result) && isset($result[0]->valor)) {
                return $result[0]->valor;
            } else {
                return '';
            }
        } catch (Exception $err) {
            throw new Exception($err->getMessage());
        }
    }

    public function load_data()
    {
        try {
            $this->appkey = env('AGENDA_APP_KEY');
            $this->domain = $this->loadCampo('domain');
        } catch (Exception $err) {
            throw new Exception($err->getMessage());
        }
    }


}

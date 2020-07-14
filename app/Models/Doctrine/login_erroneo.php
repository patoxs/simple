<?php

class LoginErroneo extends Doctrine_Record {

    function setTableDefinition() {
    	$this->hasColumn('id');
        $this->hasColumn('usuario');
        $this->hasColumn('horario');
    }

    public function findUsuarios($usuario) {

        $query = Doctrine_Query::create()
	        ->from('LoginErroneo')
	        ->where('usuario = ?', $usuario);

        return $query->execute();
    }
}

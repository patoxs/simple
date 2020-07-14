<?php

class Categoria extends Doctrine_Record {

    public function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('nombre');
        $this->hasColumn('descripcion');
        $this->hasColumn('icon_ref');
    }

}

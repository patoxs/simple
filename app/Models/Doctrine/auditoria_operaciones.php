<?php

class AuditoriaOperaciones extends Doctrine_Record
{


    function setTableDefinition()
    {
        $this->hasColumn('id');
        $this->hasColumn('fecha');
        $this->hasColumn('motivo');
        $this->hasColumn('detalles');
        $this->hasColumn('operacion');
        $this->hasColumn('usuario');
        $this->hasColumn('proceso');
        $this->hasColumn('cuenta_id');
    }

    /**
     *
     * @param type $proceso_nombre Nombre del proceso que registra
     * @param type $operacion Operación que ha sido llamada
     * @param type $motivo Detalles de la auditoría. Ej.  Registo de llmaados
     * @param type $detalles Detalles en JSON
     */
    static public function registrarAuditoria($proceso_nombre, $operacion, $motivo, $data)
    {

        $fecha = new DateTime();
        $registro_auditoria = new AuditoriaOperaciones ();
        $registro_auditoria->fecha = $fecha->format("Y-m-d H:i:s");
        $registro_auditoria->operacion = $operacion;
        $user = UsuarioSesion::usuario();
        $datauser = "anonymous@no-domain.com";
        if ($user) {
            $datauser = trim($user->nombres) == 0 ? 'Anonymous' : $user->nombres;
            $datauser .= (trim($user->apellido_paterno) != 0) ? " " . $user->apellido_paterno : "";
            $datauser .= (trim($user->apellido_materno) != 0) ? " " . $user->apellido_materno : "";
            $datauser .= (trim($user->email) != '') ? " <" . $user->email . ">" : " <anonymous@no-domain.com>";

        }
        Log::info('Usuario Registrado ' . $datauser);
        // Se necesita cambiar el usuario al usuario público.
        $registro_auditoria->usuario = $datauser;
        $registro_auditoria->proceso = $proceso_nombre;
        $registro_auditoria->cuenta_id = 1;
        $registro_auditoria->motivo = $motivo;

        //unset($accion_array['accion']['proceso_id']);
        //log_message('debug',$str_detalles);

        $registro_auditoria->detalles = json_encode($data);
        $registro_auditoria->save();
        Log::debug('Regstro guardado');
    }

}

<?php
require_once('seguridad.php');

class SeguridadForm extends Seguridad
{

    // public function displayForm() {
    //     $display='<label>Tipo de Seguridad</label>
    //                 <select id="tipoSeguridad" name="extra[tipoSeguridad]">
    //                     <option value="">Seleccione...</option>
    //                     <option value="HTTP_BASIC">HTTP_BASIC</option>
    //                     <option value="API_KEY">API_KEY</option>
    //                     <option value="OAUTH2">OAUTH2</option> 
    //                 </select>';

    //     $display.='
    //         <div class="col-md-12" id="DivUser" style="display:none;">
    //             <label>Usuario</label>
    //             <input type="text" id="user" name="extra[user]" value="'.(isset($this->extra->user) ? $this->extra->user : 'jhgjhgghjhghj').'">
    //         </div>';

    //     $display.='
    //         <div class="col-md-12" id="DivPass" style="display:none;">
    //             <label>Contraseña</label>
    //             <input type="text" id="pass" name="extra[pass] value="'.(isset($this->extra->pass) ? $this->extra->pass : 'jhgjhghjg').'">
    //         </div>';

    //     $display.='
    //         <div class="col-md-12" id="DivKey" style="display:none;">
    //             <label>Llave de aplicacion</label>
    //             <input type="text" id="key" name="extra[key]" value="'.(isset($this->extra->key) ? $this->extra->key : 'jhgjbcbcbvchggh').'">
    //         </div>';
    //     return $display;
    // }

    // public function validateForm() {
    //     $CI = & get_instance();
    //     $CI->form_validation->set_rules('institucion','Institucion2','required');
    //     $CI->form_validation->set_rules('servicio','Servicio2','required');
    //     $CI->form_validation->set_rules('extra[tipoSeguridad]', 'Tipo de seguridad2', 'required');
    // }

    //public function ejecutar(Etapa $etapa)
    public function ejecutar($tramite_id)
    {
        $etapa = Etapa::find($tramite_id);

        $regla = new Regla($this->extra->para);
        $to = $regla->getExpresionParaOutput($etapa->id);
        if (isset($this->extra->cc)) {
            $regla = new Regla($this->extra->cc);
            $cc = $regla->getExpresionParaOutput($etapa->id);
        }
        if (isset($this->extra->cco)) {
            $regla = new Regla($this->extra->cco);
            $bcc = $regla->getExpresionParaOutput($etapa->id);
        }
        $regla = new Regla($this->extra->tema);
        $subject = $regla->getExpresionParaOutput($etapa->id);
        $regla = new Regla($this->extra->contenido);
        $message = $regla->getExpresionParaOutput($etapa->id);

        $CI = &get_instance();
        $cuenta = $etapa->Tramite->Proceso->Cuenta;
        $CI->email->from($cuenta->nombre . '@' . $CI->config->item('main_domain'), $cuenta->nombre_largo);
        $CI->email->to($to);
        if (isset($cc)) $CI->email->cc($cc);
        if (isset($bcc)) $CI->email->bcc($bcc);

        if (isset($this->extra->adjunto)) {
            $attachments = explode(",", trim($this->extra->adjunto));
            foreach ($attachments as $a) {
                $regla = new Regla($a);
                $filename = $regla->getExpresionParaOutput($etapa->id);
                $file = Doctrine_Query::create()
                    ->from('File f, f.Tramite t')
                    ->where('f.filename = ? AND t.id = ?', array($filename, $etapa->Tramite->id))
                    ->fetchOne();
                if ($file) {
                    $folder = $file->tipo == 'dato' ? 'datos' : 'documentos';
                    if (file_exists('uploads/' . $folder . '/' . $filename)) {
                        $CI->email->attach('uploads/' . $folder . '/' . $filename);
                    }
                }
            }
        }

        $CI->email->subject($subject);
        $CI->email->message($message);
        $CI->email->send();
    }

}

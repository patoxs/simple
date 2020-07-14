<?php
require_once('accion.php');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AccionEnviarCorreo extends Accion
{

    public function displayForm()
    {
        $display = '<label>Para</label>';
        $display .= '<input type="text" class="form-control col-2" name="extra[para]" value="' . (isset($this->extra->para) ? $this->extra->para : '') . '" />';
        $display .= '<label>CC</label>';
        $display .= '<input type="text" class="form-control col-2" name="extra[cc]" value="' . (isset($this->extra->cc) ? $this->extra->cc : '') . '" />';
        $display .= '<label>CCO</label>';
        $display .= '<input type="text" class="form-control col-2" name="extra[cco]" value="' . (isset($this->extra->cco) ? $this->extra->cco : '') . '" />';
        $display .= '<label>Tema</label>';
        $display .= '<input type="text" class="form-control col-2" name="extra[tema]" value="' . (isset($this->extra->tema) ? $this->extra->tema : '') . '" />';
        $display .= '<label>Contenido</label>';
        $display .= '<textarea class="form-control col-2" name="extra[contenido]">' . (isset($this->extra->contenido) ? $this->extra->contenido : '') . '</textarea>';
        $display .= '<label>Adjunto (para más de un archivo separar por comas) </label>';
        $display .= '<textarea class="form-control col-2" name="extra[adjunto]">' . (isset($this->extra->adjunto) ? $this->extra->adjunto : '') . '</textarea>';

        return $display;
    }

    public function validateForm(Request $request)
    {
        $request->validate([
            'extra.para' => 'required',
            'extra.tema' => 'required',
            'extra.contenido' => 'required',
        ]);
    }

    //public function ejecutar(Etapa $etapa)
    public function ejecutar($tramite_id)
    {
        $etapa = $tramite_id;

        $regla = new Regla($this->extra->para);

        $to = $regla->getExpresionParaOutput($etapa->id);

        if (empty($to)) {
            \Log::info('Acción de tipo correo sin destinatario');
        }

        $cc = null;
        $bcc = null;

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

        $cuenta = $etapa->Tramite->Proceso->Cuenta;
        if(!empty($to)){
            Mail::send('emails.send', ['content' => $message], function ($message) use ($etapa, $subject, $cuenta, $to, $cc, $bcc) {

                $message->subject($subject);
                $mail_from = env('MAIL_FROM_ADDRESS');
                if(empty($mail_from)) {
                    $message->from($cuenta->nombre . '@' . env('APP_MAIN_DOMAIN', 'localhost'), $cuenta->nombre_largo);
                } else {
                    $message->from($mail_from);
                }

                if (!is_null($cc)) {
                    foreach (explode(',', $cc) as $cc) {
                        if (!empty($cc)) {
                            $message->cc(trim($cc));
                        }
                    }
                }

                if (!is_null($bcc)) {
                    foreach (explode(',', $bcc) as $bcc) {
                        if (!empty($bcc)) {
                            $message->bcc(trim($bcc));
                        }
                    }
                }

                if(empty(env('EMAIL_TEST')))
                    $message->to($to);
                else{
                    $destinatarios_test = explode(",",env('EMAIL_TEST'));
                    $message->to($destinatarios_test);
                }

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
                                $message->attach('uploads/' . $folder . '/' . $filename);
                            }
                        }
                    }
                }

            });
        }

    }

}
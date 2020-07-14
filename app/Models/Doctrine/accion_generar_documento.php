<?php
require_once('accion.php');

use App\Helpers\Doctrine;
use Doctrine_Query;
use Illuminate\Http\Request;

class AccionGenerarDocumento extends Accion
{

    public function displayDocumentoForm($proceso_id)
    {
        $display = '<label>Variable</label>';
        $display .= '<div class="input-group mb-2">';
        $display .= '<div class="input-group-prepend">';
        $display .= '<div class="input-group-text">@@</div>';
        $display .= '</div>';
        $display .= '<input type="text" class="form-control col-2" name="extra[variable]" value="' . ($this->extra ? $this->extra->variable : '') . '" />';
        $display .= '</div>';
        $display .= '<label>Documento</label>';
        $documentos = Doctrine::getTable('Documento')->findByProcesoId($proceso_id);
        $display .= '<select name="extra[documento_id]" class="form-control col-4">';
        $display .= '<option value=""></option>';
        foreach ($documentos as $d){
            if(!is_null($this->extra) && isset($this->extra->documento_id) && $this->extra->documento_id && $this->extra->documento_id == $d->id)
                $display .= '<option value="' . $d->id . '" selected>' . $d->nombre . '</option>';
            else
                $display .= '<option value="' . $d->id . '">' . $d->nombre . '</option>';
        }
        $display .= '</select>';
        return $display;
    }

    public function validateForm(Request $request)
    {
        $request->validate([
            'extra.documento_id' => 'required'
        ], [
            'extra.documento_id.required' => 'El campo Documento es obligatorio'
        ]);
    }

    public function ejecutar($tramite_id)
    {
        if(method_exists('Etapa', "find"))
            $etapa = Etapa::find($tramite_id); // Etapa es app/Models/Doctrine/etapa.php, no es app/Models/Etapa.php
        else
            $etapa = $tramite_id;
        
        $documento = Doctrine::getTable('Documento')->find($this->extra->documento_id);
        $dato = Doctrine::getTable('DatoSeguimiento')->findByNombreHastaEtapa($this->extra->variable, $etapa->id);
        if (!$dato) {   //Generamos el documento, ya que no se ha generado
            $file = $documento->generar($etapa->id);

            if(is_bool($file) && !$file){
                $etapa = Doctrine::getTable('Etapa')->find($etapa->id);
                $extra_etapa['error'] = false;
                $etapa->extra= json_encode($extra_etapa, true);
                $etapa->save();
                $ruta = url("/etapas/errores/{$etapa->id}");
            }else{
                $etapa = Doctrine::getTable('Etapa')->find($etapa->id);
                $extra_etapa = json_decode($etapa->extra, true);
                if(isset($extra_etapa['error'])){
                    unset($extra_etapa['error']);
                }
                $etapa->extra= json_encode($extra_etapa, true);
                $etapa->save();

                $dato = new DatoSeguimiento();
                $dato->nombre = $this->extra->variable;
                $dato->valor = $file->filename;
                $dato->etapa_id = $etapa->id;
                $dato->save();
            }

            
        }else{
            $file = Doctrine::getTable('File')->findOneByTipoAndFilename('documento', $dato->valor);
            if ($file != false) {
                $file->delete();
            }
            $file = $documento->generar($etapa->id);

            if(is_bool($file) && !$file){
                $etapa = Doctrine::getTable('Etapa')->find($etapa->id);
                $extra_etapa['error'] = false;
                $etapa->extra= json_encode($extra_etapa, true);
                $etapa->save();
                $ruta = url("/etapas/errores/{$etapa->id}");
            }else{
                $etapa = Doctrine::getTable('Etapa')->find($etapa->id);
                $extra_etapa = json_decode($etapa->extra, true);
                if(isset($extra_etapa['error'])){
                    unset($extra_etapa['error']);
                }
                $etapa->extra= json_encode($extra_etapa, true);
                $etapa->save();
                $dato->valor = $file->filename;
                $dato->save();
            }

            
        }
    }
}
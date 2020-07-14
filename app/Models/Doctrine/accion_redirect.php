<?php
require_once('accion.php');

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class AccionRedirect extends Accion
{

    public function displayForm()
    {
        $display = '<div class="input-group mb-3">';
        $display .= '<label>URL de redirección</label>';
        $display .= '<div class="input-group mb-3">';
        $display .= '<input type="text" class="form-control col-2" name="extra[url]" value="' . ($this->extra ? $this->extra->url : '') . '" />';
        $display .= '</div>';
        $display .= '</div>';

        $logout = isset($this->extra->logout) ? $this->extra->logout : null;
        $display .= '<div class="checkbox>
                        <label class="radio-inline">
                            <input type="checkbox" id="sesion" name="extra[logout]" ' . ($logout ? 'checked' : '') . ' />
                            Cierre de sesión al ejecutar la acción
                        </label>
                    </div>';
        
        return $display;
    }

    public function validateForm(Request $request)
    {
        if($request->has('extra.url')){
            $request->validate([
                'extra.url' => 'required',
            ], [
                'extra.url.required' => 'El campo url es obligatorio',
            ]);
        }
    }

    public function ejecutar($tramite_id)
    {
        $etapa = $tramite_id;

        $regla = new Regla($this->extra->url);
        $url = $regla->getExpresionParaOutput($etapa->id);

        if(isset($this->extra->logout) && Auth::user()->registrado){
            \Auth::logout();
        }
        session()->flash('redirect_url',$url);
    }

}